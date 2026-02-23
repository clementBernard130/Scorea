<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return Users::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

    
        yield TextField::new('username', 'username');
        yield TextField::new('first_name', 'Prénom');
        yield TextField::new('last_name', 'Nom');

    
    
        yield TextField::new('password', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->onlyOnForms()
            ->setRequired($pageName === Crud::PAGE_NEW)
        
            ->setFormTypeOptions(['mapped' => false]); 

    
        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices([
                'Utilisateur' => 'ROLE_USER',
                'Administrateur' => 'ROLE_ADMIN',
            ])
            ->allowMultipleChoices()
            ->renderExpanded();

    
        yield AssociationField::new('sections', 'Sections')
        ->setFormTypeOptions([
            'by_reference' => false, // Souvent nécessaire pour que les ManyToMany s'enregistrent bien
        ]);

    
        yield DateTimeField::new('created_at', 'Créé le')->hideOnForm();
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
