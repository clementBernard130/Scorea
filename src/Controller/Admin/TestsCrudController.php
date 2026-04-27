<?php

namespace App\Controller\Admin;

use App\Entity\Tests;
use App\Entity\Users;
use App\Repository\UsersRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;

class TestsCrudController extends AbstractCrudController
{
    public function __construct(
        private UsersRepository $usersRepository
    ) {}

    public static function getEntityFqcn(): string
    {
        return Tests::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('subject', 'Matière')
                ->setFormTypeOption('choice_label', 'name'),
            AssociationField::new('section', 'Section')
                ->setFormTypeOption('choice_label', 'name'),
            AssociationField::new('teacher', 'Enseignant')
                ->setFormTypeOption('choices', $this->getUsersByRole('ROLE_TEACHER'))
                ->setFormTypeOption('choice_label', fn(Users $user) => $this->formatUserLabel($user)),
            TextEditorField::new('comment')->setLabel('Commentaire'),
            DateField::new('testDate')->setLabel('Date du test'),
        ];
    }

    public function formatUserLabel(Users $user): string
    {
        $fullName = trim($user->getFirstName() . ' ' . $user->getLastName());

        return $fullName !== '' ? $fullName : (string) $user->getUsername();
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
