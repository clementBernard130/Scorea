<?php

namespace App\Controller\Admin;

use App\Entity\Tests;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TeacherTestsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tests::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityPermission('ROLE_TEACHER');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Action::INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('subject', 'Matière')
                ->setFormTypeOption('choice_label', 'name'),
            AssociationField::new('teacher', 'Enseignant')
                ->hideOnForm()
                ->formatValue(function ($value, Tests $test): string {
                    return (string) $test->getTeacher();
                }),
            TextEditorField::new('comment', 'Commentaire'),
            DateField::new('testDate', 'Date du test'),
        ];
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $teacher = $this->getTeacherUser();

        return $queryBuilder
            ->andWhere('entity.teacher = :teacher')
            ->setParameter('teacher', $teacher);
    }

    public function createEntity(string $entityFqcn)
    {
        $test = new Tests();
        $test->setTeacher($this->getTeacherUser());

        return $test;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tests) {
            throw new \InvalidArgumentException('Instance non valide pour TeacherTestsCrudController.');
        }

        $entityInstance->setTeacher($this->getTeacherUser());

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tests) {
            throw new \InvalidArgumentException('Instance non valide pour TeacherTestsCrudController.');
        }

        $teacher = $this->getTeacherUser();
        if ($entityInstance->getTeacher()?->getId() !== $teacher->getId()) {
            throw new AccessDeniedException('Vous ne pouvez modifier que vos propres tests.');
        }

        $entityInstance->setTeacher($teacher);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tests) {
            throw new \InvalidArgumentException('Instance non valide pour TeacherTestsCrudController.');
        }

        $teacher = $this->getTeacherUser();
        if ($entityInstance->getTeacher()?->getId() !== $teacher->getId()) {
            throw new AccessDeniedException('Vous ne pouvez supprimer que vos propres tests.');
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $test = $entityDto->getInstance();
        if (!$test instanceof Tests || $test->getTeacher()?->getId() !== $this->getTeacherUser()->getId()) {
            throw new AccessDeniedException('Accès refusé à ce test.');
        }

        return parent::createEditFormBuilder($entityDto, $formOptions, $context);
    }

    private function getTeacherUser(): Users
    {
        $user = $this->getUser();

        if (!$user instanceof Users || !in_array('ROLE_TEACHER', $user->getRoles(), true)) {
            throw new AccessDeniedException('Accès réservé aux enseignants.');
        }

        return $user;
    }
}