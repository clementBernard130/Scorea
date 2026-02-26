<?php

namespace App\Controller\Admin;

use App\Entity\Sections;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
            ->setEntityLabelInSingular('Section')
            ->setEntityLabelInPlural('Sections')
            ->setDefaultSort(['start_date' => 'DESC']);
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

            AssociationField::new('users', 'Utilisateurs')
                ->setTemplatePath('admin/field/users_badges.html.twig')
                ->onlyOnIndex(),
                
            AssociationField::new('users', 'Utilisateurs')
                ->autocomplete()
                ->setFormTypeOption('by_reference', false)
                ->onlyOnForms(),
        ];
    }
}