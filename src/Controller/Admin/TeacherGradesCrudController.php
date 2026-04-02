<?php

namespace App\Controller\Admin;

use App\Entity\Grades;
use App\Entity\Tests;
use App\Entity\Users;
use App\Repository\TestsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TeacherGradesCrudController extends AbstractCrudController
{
    public function __construct(
        private UsersRepository $usersRepository,
        private TestsRepository $testsRepository
    ) {}

    public static function getEntityFqcn(): string
    {
        return Grades::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Action::INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityPermission('ROLE_TEACHER');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            NumberField::new('grade', 'Note'),
            AssociationField::new('test', 'Évaluation')
                ->setFormTypeOption('choices', $this->getTeacherTests())
                ->setFormTypeOption('choice_label', static function (Tests $test): string {
                    return sprintf(
                        '%s - %s',
                        $test->getSubject()?->getName() ?? 'Matière inconnue',
                        $test->getTestDate()?->format('d/m/Y') ?? 'Date inconnue'
                    );
                }),
            AssociationField::new('student', 'Élève')
                ->setFormTypeOption('choices', $this->getTeacherStudents())
                ->setFormTypeOption('choice_label', function (Users $user): string {
                    $fullName = trim(sprintf('%s %s', $user->getFirstName(), $user->getLastName()));

                    return $fullName !== '' ? $fullName : (string) $user->getUsername();
                })
                ->formatValue(function ($value, Grades $grade): string {
                    return (string) $grade->getStudent();
                }),
            AssociationField::new('gradeType', 'Type de note')
                ->setFormTypeOption('choice_label', 'name'),
            TextEditorField::new('comment', 'Commentaire')
                ->hideOnIndex(),
        ];
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $teacher = $this->getTeacherUser();

        return $queryBuilder
            ->innerJoin('entity.test', 'test')
            ->andWhere('test.teacher = :teacher')
            ->setParameter('teacher', $teacher);
    }

    public function createEntity(string $entityFqcn)
    {
        return new Grades();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Grades) {
            throw new \InvalidArgumentException('Instance non valide pour TeacherGradesCrudController.');
        }

        $teacher = $this->getTeacherUser();
        if ($entityInstance->getTest()?->getTeacher()?->getId() !== $teacher->getId()) {
            throw new AccessDeniedException('Vous ne pouvez saisir des notes que pour vos propres tests.');
        }

        $now = new \DateTimeImmutable();
        $entityInstance->setCreatedAt($entityInstance->getCreatedAt() ?? $now);
        $entityInstance->setUpdatedAt($now);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Grades) {
            throw new \InvalidArgumentException('Instance non valide pour TeacherGradesCrudController.');
        }

        $teacher = $this->getTeacherUser();
        if ($entityInstance->getTest()?->getTeacher()?->getId() !== $teacher->getId()) {
            throw new AccessDeniedException('Vous ne pouvez modifier que vos propres notes.');
        }

        $entityInstance->setUpdatedAt(new \DateTimeImmutable());

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Grades) {
            throw new \InvalidArgumentException('Instance non valide pour TeacherGradesCrudController.');
        }

        $teacher = $this->getTeacherUser();
        if ($entityInstance->getTest()?->getTeacher()?->getId() !== $teacher->getId()) {
            throw new AccessDeniedException('Vous ne pouvez supprimer que vos propres notes.');
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $grade = $entityDto->getInstance();
        if (!$grade instanceof Grades || $grade->getTest()?->getTeacher()?->getId() !== $this->getTeacherUser()->getId()) {
            throw new AccessDeniedException('Accès refusé à cette note.');
        }

        return parent::createEditFormBuilder($entityDto, $formOptions, $context);
    }

    private function getTeacherUser(): Users
    {
        $user = $this->getUser();
        if (!$user instanceof Users || !in_array('ROLE_TEACHER', $user->getRoles(), true)) {
            throw new AccessDeniedException('Accès réservé aux enseignants.');
        }

        return $user;
    }

    /**
     * @return Users[]
     */
    private function getTeacherStudents(): array
    {
        $studentIds = $this->usersRepository->findStudentIdsForTeacher($this->getTeacherUser());
        if ($studentIds === []) {
            return [];
        }

        $users = $this->usersRepository->findBy(['id' => $studentIds]);

        usort($users, static function (Users $a, Users $b): int {
            $aLastName = (string) $a->getLastName();
            $bLastName = (string) $b->getLastName();

            $byLastName = strcasecmp($aLastName, $bLastName);
            if ($byLastName !== 0) {
                return $byLastName;
            }

            return strcasecmp((string) $a->getFirstName(), (string) $b->getFirstName());
        });

        return $users;
    }

    /**
     * @return Tests[]
     */
    private function getTeacherTests(): array
    {
        return $this->testsRepository->findByTeacher($this->getTeacherUser());
    }
}
