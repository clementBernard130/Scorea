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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;

class TrainingCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return Trainings::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('Formation')
            ->setEntityLabelInPlural('Formations')
            ->setPageTitle('index', 'Liste des formations')
            ->setPageTitle('new', 'Créer une formation')
            ->setPageTitle('edit', 'Modifier la formation')
            ->setPageTitle('detail', 'Détails de la formation')
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name'])
            ->overrideTemplates([
                'crud/new' => 'admin/trainings/training_new.html.twig',
                'crud/edit' => 'admin/trainings/training_edit.html.twig',
                'crud/detail' => 'admin/trainings/training_detail.html.twig',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        $skillUnitsAction = Action::new('skillUnits', 'Blocs de competences')
            ->setIcon('fa fa-cubes')
            ->linkToUrl(function (?Trainings $training): string {
                if (!$training) {
                    return '';
                }

                return $this->adminUrlGenerator->unsetAll()
                    ->setController(SkillUnitCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('trainingId', $training->getId())
                    ->generateUrl();
            });

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
            )
            ->add(Crud::PAGE_DETAIL, $skillUnitsAction)
            ->add(Crud::PAGE_EDIT, $skillUnitsAction);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm()
            ->hideOnIndex();

        yield TextField::new('name', 'Nom')
            ->setRequired(true);

        yield TextareaField::new('description', 'Description')
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

        yield AssociationField::new('skillUnits', 'Blocs de competences')
            ->onlyOnDetail();

        yield AssociationField::new('skillUnits', 'Blocs de competences')
            ->autocomplete()
            ->setFormTypeOption('by_reference', false)
            ->setRequired(false)
            ->onlyWhenUpdating();
    }
}
