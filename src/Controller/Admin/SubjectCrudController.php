<?php

namespace App\Controller\Admin;

use App\Entity\Subjects;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SubjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Subjects::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('Matière')
            ->setEntityLabelInPlural('Matières')
            ->setPageTitle('index', 'Liste des matières')
            ->setPageTitle('new', 'Créer une matière')
            ->setPageTitle('edit', 'Modifier la matière')
            ->setPageTitle('detail', 'Détails de la matière')
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name', 'description'])
            ->overrideTemplates([
                'crud/new' => 'admin/subjects/subject_new.html.twig',
                'crud/edit' => 'admin/subjects/subject_edit.html.twig',
                'crud/detail' => 'admin/subjects/subject_detail.html.twig',
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('coefficient');
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

            TextField::new('name', 'Nom'),

            TextEditorField::new('description', 'Description')
                ->hideOnIndex(),

            NumberField::new('coefficient', 'Coefficient'),

            IntegerField::new('id', 'Nombre de tests')
                ->onlyOnIndex()
                ->setSortable(false)
                ->formatValue(static fn ($value, Subjects $subject): string => (string) $subject->getTests()->count()),
        ];
    }
}
