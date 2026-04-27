<?php

namespace App\Controller\Admin;

use App\Entity\Tests;
use App\Entity\Users;
use App\Repository\UsersRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('Test')
            ->setEntityLabelInPlural('Tests')
            ->setPageTitle('index', 'Liste des tests')
            ->setPageTitle('new', 'Créer un test')
            ->setPageTitle('edit', 'Modifier le test')
            ->setPageTitle('detail', 'Détails du test')
            ->setDefaultSort(['testDate' => 'DESC'])
            ->setSearchFields(['comment'])
            ->overrideTemplates([
                'crud/new' => 'admin/tests/test_new.html.twig',
                'crud/edit' => 'admin/tests/test_edit.html.twig',
                'crud/detail' => 'admin/tests/test_detail.html.twig',
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('subject')
            ->add('section')
            ->add('teacher')
            ->add('testDate');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, static fn (Action $action): Action => $action
                ->setIcon('fa fa-eye')
                ->setLabel(false)
                ->setHtmlAttributes(['title' => 'Consulter'])
            );
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex(),
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
