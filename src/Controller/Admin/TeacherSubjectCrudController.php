<?php

namespace App\Controller\Admin;

use App\Entity\Subjects;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TeacherSubjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Subjects::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Action::INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityPermission('ROLE_TEACHER');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            TextEditorField::new('description', 'Description'),
            NumberField::new('coefficient', 'Coefficient'),
        ];
    }
}
