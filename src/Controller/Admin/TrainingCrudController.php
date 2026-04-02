<?php

namespace App\Controller\Admin;

use App\Entity\Trainings;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TrainingCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

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

    public function configureActions(Actions $actions): Actions
    {
        $skillUnitsAction = Action::new('skillUnits', 'Blocs de competences')
            ->setIcon('fa fa-cubes')
            ->linkToUrl(function (?Trainings $training): string {
                if (!$training) return '';
                
                $action = $training->getSkillUnits()->isEmpty() ? Action::NEW : Action::INDEX;
                
                return $this->adminUrlGenerator->unsetAll()
                    ->setController(SkillUnitCrudController::class)
                    ->setAction($action)->set('trainingId', $training->getId())->generateUrl();
            });

        return $actions->add(Crud::PAGE_INDEX, $skillUnitsAction)
            ->add(Crud::PAGE_DETAIL, $skillUnitsAction)
            ->add(Crud::PAGE_NEW, $skillUnitsAction)
            ->add(Crud::PAGE_EDIT, $skillUnitsAction);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield TextField::new('name', 'Nom')
            ->setRequired(true);

        yield TextEditorField::new('description', 'Description')
            ->hideOnIndex()
            ->setRequired(false);

        yield IntegerField::new('id', 'Nombre de sections')
            ->onlyOnIndex()
            ->setSortable(false)
            ->formatValue(static fn ($value, Trainings $training): string => (string) $training->getSections()->count());

        yield IntegerField::new('id', 'Nombre de blocs')
            ->onlyOnIndex()
            ->setSortable(false)
            ->formatValue(static fn ($value, Trainings $training): string => (string) $training->getSkillUnits()->count());

        yield AssociationField::new('sections', 'Sections')
            ->onlyOnDetail();

        yield AssociationField::new('sections', 'Sections')
            ->autocomplete()
            ->setFormTypeOption('by_reference', false)
            ->setRequired(false)
            ->onlyOnForms();

        yield AssociationField::new('skillUnits', 'Blocs de competences')
            ->onlyOnDetail();

        yield AssociationField::new('skillUnits', 'Blocs de competences')
            ->autocomplete()
            ->setFormTypeOption('by_reference', false)
            ->setRequired(false)
            ->onlyOnForms();
    }
}
