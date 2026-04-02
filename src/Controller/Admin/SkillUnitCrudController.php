<?php

namespace App\Controller\Admin;

use App\Entity\Trainings;
use App\Form\SkillType;
use App\Entity\SkillsUnit;
use App\Repository\TrainingsRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SkillUnitCrudController extends AbstractCrudController
{
    public function __construct(
        private TrainingsRepository $trainingsRepository,
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return SkillsUnit::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action
                ->linkToUrl(fn (): string => $this->generateContextualNewUrl())
            )
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $action) => $action
                ->linkToUrl(fn (SkillsUnit $skillUnit): string => $this->generateContextualUrl(Action::EDIT, $skillUnit))
            )
            ->update(Crud::PAGE_DETAIL, Action::EDIT, fn (Action $action) => $action
                ->linkToUrl(fn (SkillsUnit $skillUnit): string => $this->generateContextualUrl(Action::EDIT, $skillUnit))
            )
            ->update(Crud::PAGE_DETAIL, Action::INDEX, fn (Action $action) => $action
                ->linkToUrl(fn (SkillsUnit $skillUnit): string => $this->generateContextualIndexUrl($skillUnit))
            );
    }

    public function createEntity(string $entityFqcn): SkillsUnit
    {
        $skillUnit = new SkillsUnit();
        $trainingId = $this->getContext()?->getRequest()->query->get('trainingId');

        if ($trainingId !== null) {
            $training = $this->trainingsRepository->find($trainingId);

            if ($training instanceof Trainings) {
                $skillUnit->setTrainings($training);
            }
        }

        return $skillUnit;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $trainingId = $this->getContext()?->getRequest()->query->get('trainingId');

        if ($trainingId !== null) {
            $queryBuilder
                ->andWhere('entity.trainings = :trainingId')
                ->setParameter('trainingId', $trainingId);
        }

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        $trainingId = $this->getContext()?->getRequest()->query->get('trainingId');

        $trainingField = AssociationField::new('trainings', 'Formation')
            ->autocomplete()
            ->setRequired(false);

        if ($trainingId !== null && Crud::PAGE_NEW === $pageName) {
            $trainingField->setHelp('Formation préremplie depuis la formation d’origine.');
        }

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom du Bloc'),
            $trainingField,
            TextEditorField::new('description', 'Description'),
            CollectionField::new('skills', 'Compétences')
                ->setEntryType(SkillType::class) // Votre formulaire Symfony
                ->setFormTypeOption('by_reference', false)
                ->allowAdd(true)
                ->allowDelete(true)
                ->renderExpanded(true)
                ->setEntryIsComplex(true),

            AssociationField::new('skills', 'Compétences')
                ->onlyOnIndex()
                ->formatValue(function ($value, SkillsUnit $entity) {
                    $count = $entity->getSkills()->count();
                    if ($count === 0) {
                        return 'Aucune';
                    }

                    $names = array_slice(
                        $entity->getSkills()->map(fn($skill) => $skill->getName())->toArray(), 0, 3
                    );

                    $namesList = implode(', ', $names);

                    return $count > 3
                        ? "$namesList (+".($count - 3)." de plus)"
                        : ($namesList ?: 'Aucune');
                }),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        $trainingId = $this->getContext()?->getRequest()->query->get('trainingId');

        return $crud
            ->setPageTitle('index', $trainingId !== null ? 'Blocs de compétences de la formation' : 'Blocs de compétences')
            ->setPageTitle('new', $trainingId !== null ? 'Créer un bloc pour la formation' : 'Créer un bloc')
            ->setPageTitle('edit', 'Modifier le bloc');
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $trainingId = $context->getRequest()->query->get('trainingId');

        if ($trainingId !== null) {
            $submitButtonName = $context->getRequest()->request->all()['ea']['newForm']['btn'] ?? null;

            $url = match ($submitButtonName) {
                Action::SAVE_AND_CONTINUE => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(self::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($context->getEntity()->getPrimaryKeyValue())
                    ->set('trainingId', $trainingId)
                    ->generateUrl(),
                Action::SAVE_AND_ADD_ANOTHER => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(self::class)
                    ->setAction(Action::NEW)
                    ->set('trainingId', $trainingId)
                    ->generateUrl(),
                default => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(self::class)
                    ->setAction(Action::INDEX)
                    ->set('trainingId', $trainingId)
                    ->generateUrl(),
            };

            return $this->redirect($url);
        }

        return parent::getRedirectResponseAfterSave($context, $action);
    }

    private function generateContextualUrl(string $action, SkillsUnit $skillUnit): string
    {
        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction($action)
            ->setEntityId($skillUnit->getId());

        $trainingId = $this->getContext()?->getRequest()->query->get('trainingId');

        if ($trainingId !== null) {
            $url->set('trainingId', $trainingId);
        }

        return $url->generateUrl();
    }

    private function generateContextualNewUrl(): string
    {
        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::NEW);

        $trainingId = $this->getContext()?->getRequest()->query->get('trainingId');

        if ($trainingId !== null) {
            $url->set('trainingId', $trainingId);
        }

        return $url->generateUrl();
    }

    private function generateContextualIndexUrl(SkillsUnit $skillUnit): string
    {
        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::INDEX);

        $trainingId = $this->getContext()?->getRequest()->query->get('trainingId')
            ?? $skillUnit->getTrainings()?->getId();

        if ($trainingId !== null) {
            $url->set('trainingId', $trainingId);
        }

        return $url->generateUrl();
    }
}
