<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Repository\GradesRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TeacherStudentCrudController extends AbstractCrudController
{
    /**
     * @var array<int, float|null>
     */
    private array $averageCache = [];

    public function __construct(
        private GradesRepository $gradesRepository,
        private UsersRepository $usersRepository
    ) {}

    public static function getEntityFqcn(): string
    {
        return Users::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_TEACHER')
            ->setEntityLabelInSingular('Élève')
            ->setEntityLabelInPlural('Mes élèves');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('first_name', 'Prénom'),
            TextField::new('last_name', 'Nom'),
            NumberField::new('id', 'Moyenne')
                ->setNumDecimals(2)
                ->formatValue(function ($value, Users $student): string {
                    $teacher = $this->getTeacherUser();
                    $studentId = $student->getId();

                    if ($studentId === null) {
                        return '—';
                    }

                    if (!array_key_exists($studentId, $this->averageCache)) {
                        $this->averageCache[$studentId] = $this->gradesRepository
                            ->getAverageByStudentForTeacher($studentId, (int) $teacher->getId());
                    }

                    $average = $this->averageCache[$studentId];

                    return $average === null ? '—' : number_format($average, 2, ',', ' ');
                }),
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
        $studentIds = $this->usersRepository->findStudentIdsForTeacher($teacher);

        if ($studentIds === []) {
            return $queryBuilder->andWhere('1 = 0');
        }

        return $queryBuilder
            ->andWhere('entity.id IN (:studentIds)')
            ->setParameter('studentIds', $studentIds);
    }

    private function getTeacherUser(): Users
    {
        $user = $this->getUser();
        if (!$user instanceof Users || !in_array('ROLE_TEACHER', $user->getRoles(), true)) {
            throw new AccessDeniedException('Accès réservé aux enseignants.');
        }

        return $user;
    }
}
