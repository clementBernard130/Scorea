<?php

namespace App\Controller\Admin;

use App\Entity\Grades;
use App\Entity\GradeTypeNames;
use App\Entity\Tests;
use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Service\AlertService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class GradesCrudController extends AbstractCrudController
{
    public function __construct(
        private UsersRepository $usersRepository,
        private AlertService $alertService,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Grades::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Grades && !$entityInstance->getCreatedAt()) {
            $now = new \DateTimeImmutable();
            $entityInstance->setCreatedAt($now);
            $entityInstance->setUpdatedAt($now);
        }

        parent::persistEntity($entityManager, $entityInstance);

        if ($entityInstance instanceof Grades) {
            $this->alertService->handleGradeCreated($entityInstance);
        }
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            NumberField::new('grade', 'Note'),
            AssociationField::new('student', 'Eleve')
                ->setFormTypeOption('choices', $this->getUsersByRole('ROLE_STUDENT'))
                ->setFormTypeOption('choice_label', fn(Users $u) => $this->formatUserLabel($u)),
            AssociationField::new('test', 'Test')
                ->setFormTypeOption('choice_label', fn(Tests $test) => $this->formatTestLabel($test)),
            AssociationField::new('gradeType', 'Type de note')
                ->setFormTypeOption('choice_label', fn(GradeTypeNames $gradeType) => $this->formatGradeTypeLabel($gradeType)),
        ];
    }

    public function formatUserLabel(Users $user): string
    {
        $fullName = trim($user->getFirstName() . ' ' . $user->getLastName());

        return $fullName !== '' ? $fullName : (string) $user->getUsername();
    }

    public function formatTestLabel(Tests $test): string
    {
        $subjectName = $test->getSubject()?->getName() ?? 'Matiere inconnue';
        $teacherName = $test->getTeacher() !== null
            ? $this->formatUserLabel($test->getTeacher())
            : 'Professeur inconnu';
        $testDate = $test->getTestDate()?->format('d/m/Y') ?? 'Date inconnue';

        return sprintf('%s - %s - %s', $subjectName, $teacherName, $testDate);
    }

    public function formatGradeTypeLabel(GradeTypeNames $gradeType): string
    {
        return $gradeType->getName() ?? 'Type inconnu';
    }

    /**
     * @return Users[]
     */
    private function getUsersByRole(string $role): array
    {
        $users = array_filter(
            $this->usersRepository->findAll(),
            static fn (Users $user): bool => in_array($role, $user->getRoles(), true)
        );

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
}
