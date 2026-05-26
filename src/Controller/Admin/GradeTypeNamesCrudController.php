<?php

namespace App\Controller\Admin;

use App\Entity\GradeTypeNames;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GradeTypeNamesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GradeTypeNames::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Type de note')
            ->setEntityLabelInPlural('Types de note')
            ->setPageTitle('index', 'Types de note')
            ->setPageTitle('new', 'Créer un type de note')
            ->setPageTitle('edit', 'Modifier le type de note')
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex(),
            TextField::new('name', 'Nom'),
        ];
    }
}