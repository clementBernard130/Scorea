<?php

namespace App\Controller\Admin;

use App\Entity\Grades;
use App\Entity\Users;
use App\Repository\UsersRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class GradesCrudController extends AbstractCrudController
{
    public function __construct(
        private UsersRepository $usersRepository
    ) {}

    public static function getEntityFqcn(): string
    {
        return Grades::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            NumberField::new('grade', 'Note'),
            AssociationField::new('student', 'Eleve')
                ->setFormTypeOption('choices', $this->getUsersByRole('ROLE_STUDENT'))
                ->setFormTypeOption('choice_label', function (Users $user): string {
                    $fullName = trim(sprintf('%s %s', $user->getFirstName(), $user->getLastName()));

                    return $fullName !== '' ? $fullName : (string) $user->getUsername();
                })
                ->formatValue(function ($value, Grades $grade): string {
                    return (string) $grade->getStudent();
                }),
            AssociationField::new('subject', 'Matiere')
                ->setFormTypeOption('choice_label', 'name')
                ->formatValue(function ($value, Grades $grade): string {
                    return (string) ($grade->getSubject()?->getName() ?? '');
                }),
            AssociationField::new('teacher', 'Professeur')
                ->setFormTypeOption('choices', $this->getUsersByRole('ROLE_TEACHER'))
                ->setFormTypeOption('choice_label', function (Users $user): string {
                    $fullName = trim(sprintf('%s %s', $user->getFirstName(), $user->getLastName()));

                    return $fullName !== '' ? $fullName : (string) $user->getUsername();
                })
                ->formatValue(function ($value, Grades $grade): string {
                    return (string) $grade->getTeacher();
                }),
        ];
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
