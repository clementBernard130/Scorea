<?php

namespace App\Controller\Admin;

use App\Entity\Trainings;
use App\Form\SkillType;
use App\Entity\SkillsUnit;
use App\Repository\SkillsUnitRepository;
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
        private SkillsUnitRepository $skillsUnitRepository,
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return SkillsUnit::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE, fn (Action $action) => $action
                ->asPrimaryAction()
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn (Action $action) => $action
                ->asDefaultAction()
            )
            ->reorder(Crud::PAGE_NEW, [Action::SAVE_AND_CONTINUE, Action::SAVE_AND_RETURN])
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
        $trainingId = $this->getTrainingId();

        if ($trainingId !== null) {
            $training = $this->trainingsRepository->find($trainingId);

            if ($training instanceof Trainings) {
                $skillUnit->setTrainings($training);
            }
        }

        return $skillUnit;
    }

    public function index(AdminContext $context)
    {
        $trainingId = $this->getTrainingId();

        if ($trainingId !== null && 0 === $this->skillsUnitRepository->count(['trainings' => $trainingId])) {
            return $this->redirect($this->generateContextualNewUrl());
        }

        return parent::index($context);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $trainingId = $this->getTrainingId();

        if ($trainingId !== null) {
            $queryBuilder
                ->andWhere('entity.trainings = :trainingId')
                ->setParameter('trainingId', $trainingId);
        }

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        $trainingId = $this->getTrainingId();

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
        return $crud
            ->setPageTitle('index', fn () => $this->buildPageTitle('index'))
            ->setPageTitle('new', fn () => $this->buildPageTitle('new'))
            ->setPageTitle('edit', fn (?SkillsUnit $skillUnit) => $this->buildPageTitle('edit', $skillUnit));
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $trainingId = $context->getRequest()->query->get('trainingId')
            ?? $context->getRequest()->attributes->get('trainingId');

        if ($trainingId !== null) {
            $requestData = $context->getRequest()->request->all()['ea'] ?? [];
            $submitButtonName = $requestData['newForm']['btn'] ?? $requestData['editForm']['btn'] ?? null;

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
                default => Action::NEW === $action
                    ? $this->adminUrlGenerator
                        ->unsetAll()
                        ->setController(self::class)
                        ->setAction(Action::EDIT)
                        ->setEntityId($context->getEntity()->getPrimaryKeyValue())
                        ->set('trainingId', $trainingId)
                        ->generateUrl()
                    : $this->adminUrlGenerator
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

        $trainingId = $this->getTrainingId();

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

        $trainingId = $this->getTrainingId();

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

        $trainingId = $this->getTrainingId()
            ?? $skillUnit->getTrainings()?->getId();

        if ($trainingId !== null) {
            $url->set('trainingId', $trainingId);
        }

        return $url->generateUrl();
    }

    private function getTrainingId(): ?string
    {
        $request = $this->getContext()?->getRequest();

        if ($request === null) {
            return null;
        }

        return $request->query->get('trainingId')
            ?? $request->attributes->get('trainingId');
    }

    private function buildPageTitle(string $pageName, ?SkillsUnit $skillUnit = null): string
    {
        $trainingId = $this->getTrainingId() ?? $skillUnit?->getTrainings()?->getId();
        $training = $trainingId !== null ? $this->trainingsRepository->find($trainingId) : null;
        $trainingName = $training?->getName();

        return match ($pageName) {
            'index' => $trainingName !== null
                ? sprintf('Blocs de compétences de la formation : %s', $trainingName)
                : 'Blocs de compétences',
            'new' => $trainingName !== null
                ? sprintf('Créer un bloc pour la formation : %s', $trainingName)
                : 'Créer un bloc',
            'edit' => $trainingName !== null
                ? sprintf('Modifier le bloc de la formation : %s', $trainingName)
                : 'Modifier le bloc',
            default => 'Blocs de compétences',
        };
    }
}
