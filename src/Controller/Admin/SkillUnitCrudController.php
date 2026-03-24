<?php

namespace App\Controller\Admin;

use App\Entity\SkillsUnit;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SkillUnitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SkillsUnit::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom du Bloc'),
            TextEditorField::new('description', 'Description'),
            AssociationField::new('skills', 'Compétences associées')
                ->onlyOnForms()
                ->setFormTypeOption('by_reference', false)
                ->setCrudController(SkillCrudController::class) 
                ->autocomplete()

                ->setHelp('Recherchez et associez des compétences existantes à ce bloc.'),
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
            ->setPageTitle('index', 'Blocs de compétences')
            ->setPageTitle('new', 'Créer un bloc')
            ->setPageTitle('edit', 'Modifier le bloc');
    }

}
