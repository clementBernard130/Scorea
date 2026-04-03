<?php

namespace App\Controller\Admin;

use App\Entity\Sections;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SectionsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sections::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('Section')
            ->setEntityLabelInPlural('Sections')
            ->setPageTitle('index', 'Liste des sections')
            ->setPageTitle('new', 'Créer une section')
            ->setPageTitle('edit', 'Modifier la section')
            ->setPageTitle('detail', 'Détails de la section')
            ->setDefaultSort(['start_date' => 'DESC'])
            ->setSearchFields(['name'])
            ->overrideTemplates([
                'crud/new' => 'admin/sections/section_new.html.twig',
                'crud/edit' => 'admin/sections/section_edit.html.twig',
                'crud/detail' => 'admin/sections/section_detail.html.twig',
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('training')
            ->add('start_date')
            ->add('end_date');
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

            DateField::new('start_date', 'Date de début'),
            DateField::new('end_date', 'Date de fin'),

            AssociationField::new('training', 'Formation')
                ->setRequired(true),

            IntegerField::new('id', 'Nombre d\'utilisateurs')
                ->setSortable(false)
                ->formatValue(static fn ($value, Sections $section): string => (string) $section->getUsers()->count())
                ->onlyOnIndex(),
                
            AssociationField::new('users', 'Utilisateurs')
                ->autocomplete()
                ->setFormTypeOption('by_reference', false)
                ->onlyOnForms(),
        ];
    }
}