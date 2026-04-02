<?php

namespace App\Controller\Admin;

use App\Entity\Trainings;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TrainingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Trainings::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Formation')
            ->setEntityLabelInPlural('Formations')
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name', 'description']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield TextField::new('name', 'Nom')
            ->setRequired(true)
            ->setHelp('Nom affiché dans l’administration et dans les listes.');

        yield TextEditorField::new('description', 'Description')
            ->hideOnIndex()
            ->setRequired(false);

        yield IntegerField::new('id', 'Nombre de sections')
            ->onlyOnIndex()
            ->setSortable(false)
            ->formatValue(static fn ($value, Trainings $training): string => (string) $training->getSections()->count());

        yield AssociationField::new('sections', 'Sections')
            ->onlyOnDetail();

        yield AssociationField::new('sections', 'Sections')
            ->autocomplete()
            ->setFormTypeOption('by_reference', false)
            ->setRequired(false)
            ->onlyOnForms();
    }
}
