<?php

namespace App\Controller\Admin;

use App\Entity\Skills;
use App\Repository\SkillsRepository;
use App\Repository\SkillsUnitRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class SkillsCrudController extends AbstractCrudController
{
    public function __construct(
        private SkillsRepository $skillsRepository,
        private SkillsUnitRepository $skillsUnitRepository,
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return Skills::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn (Action $action) => $action
                ->setIcon('fa fa-eye')
                ->setLabel(false)
                ->setHtmlAttributes(['title' => 'Consulter'])
            );
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('name', 'Nom'),
            AssociationField::new('skillUnit', 'Bloc')->autocomplete()->setRequired(false),
            TextareaField::new('description', 'Description')->setRequired(false),
            AssociationField::new('subjects', 'Matières')->autocomplete()->setRequired(false)->hideOnIndex(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setPageTitle('index', 'Compétences')
            ->setPageTitle('new', 'Créer une compétence')
            ->setPageTitle('edit', 'Modifier la compétence')
            ->setPageTitle('detail', 'Détails de la compétence')
            ->overrideTemplates([
                'crud/detail' => 'admin/skills/skills_detail.html.twig',
                'crud/new' => 'admin/skills/skills_new.html.twig',
                'crud/edit' => 'admin/skills/skills_edit.html.twig',
            ]);
    }
}
