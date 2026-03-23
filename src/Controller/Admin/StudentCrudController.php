<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class StudentCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return Users::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, static fn (Action $action): Action => $action
                ->setIcon('fa fa-eye')
                ->setLabel(false)
                ->setHtmlAttributes(['title' => 'Consulter'])
            )
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->disable(Action::DELETE);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('username')
            ->add('first_name')
            ->add('last_name')
            ->add('sections');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('Étudiant')
            ->setEntityLabelInPlural('Étudiants')
            ->setPageTitle('index', 'Gestion des étudiants')
            ->setPageTitle('new', 'Créer un étudiant')
            ->setPageTitle('edit', 'Modifier un étudiant')
            ->setPageTitle('detail', 'Détails de l\'utilisateur')
            ->setSearchFields(['username', 'first_name', 'last_name'])
            ->setDefaultSort(['created_at' => 'DESC'])
            ->overrideTemplates([
                'crud/detail' => 'admin/user_detail.html.twig',
                'crud/edit' => 'admin/user_edit.html.twig',
            ]);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $connection = $qb->getEntityManager()->getConnection();
        $platform = $connection->getDatabasePlatform();
        $isPostgreSql = str_contains(get_debug_type($platform), 'PostgreSQL');

        $castExpression = 'CAST(roles AS CHAR)';
        if ($isPostgreSql) {
            $castExpression = 'CAST(roles AS TEXT)';
        }

        $sql = sprintf('SELECT id FROM users WHERE %s LIKE :role', $castExpression);
        $roleIds = $connection->fetchFirstColumn($sql, ['role' => '%"ROLE_STUDENT"%']);

        if (empty($roleIds)) {
            return $qb->andWhere('1 = 0');
        }

        return $qb
            ->andWhere('entity.id IN (:roleIds)')
            ->setParameter('roleIds', array_map('intval', $roleIds), ArrayParameterType::INTEGER);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm()->hideOnIndex();

        yield TextField::new('last_name', 'Nom');
        yield TextField::new('first_name', 'Prénom');
        yield TextField::new('email', 'Email')->hideOnIndex();

        yield TextField::new('password', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->onlyOnForms()
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->setFormTypeOptions(['mapped' => false]); 

        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices([
                'Administrateur' => 'ROLE_ADMIN',
                'Professeur' => 'ROLE_TEACHER',
                "Etudiant" => 'ROLE_STUDENT',
            ])
            ->allowMultipleChoices()
            ->renderExpanded()
            ->renderAsBadges([
                'ROLE_ADMIN' => 'success',
                'ROLE_TEACHER' => 'warning',
                'ROLE_STUDENT' => 'info',
            ])->hideOnIndex();

        yield AssociationField::new('sections', 'Sections')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])
            ->setTemplatePath('admin/field/sections.html.twig');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        
    
        if ($entityInstance instanceof Users && !$entityInstance->getCreatedAt()) {
            $entityInstance->setCreatedAt(new \DateTimeImmutable());
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * Cette méthode est appelée lors de la modification d'un utilisateur
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        
    
        if ($entityInstance instanceof Users) {
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * Méthode privée pour gérer le hachage
     */
    private function hashPassword($user): void
    {
        if (!$user instanceof Users) {
            return;
        } 
        
        $context = $this->getContext();
        $plainPassword = $context->getRequest()->request->all('Users')['password'] ?? null;

        if (!empty($plainPassword)) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }
    }
}
