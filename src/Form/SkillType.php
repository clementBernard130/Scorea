<?php

namespace App\Form;

use App\Entity\GradeTypeNames;
use App\Entity\Skills;
use App\Entity\Subjects;
use App\Repository\SubjectsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillType extends AbstractType
{
    public function __construct(
        private SubjectsRepository $subjectsRepository
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la compétence',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('subjects', EntityType::class, [
                'class' => Subjects::class,
                'choice_label' => 'name',
                'label' => 'Matières existantes',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                ],
            ])
            ->add('gradeTypes', CollectionType::class, [
                'entry_type' => SkillWeightType::class,
                'label' => 'Types d\'épreuve et poids (%)',
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ])

            ->add('newSubjectName', TextType::class, [
                'label' => 'Nouvelle matière',
                'mapped' => false,
                'required' => false,
            ])
            ->add('newSubjectDescription', TextareaType::class, [
                'label' => 'Description de la nouvelle matière',
                'mapped' => false,
                'required' => false,
                'empty_data' => '',
            ])
            ->add('newSubjectCoefficient', NumberType::class, [
                'label' => 'Coefficient de la nouvelle matière',
                'mapped' => false,
                'required' => false,
                'html5' => true,
                'scale' => 2,
                'attr' => [
                    'inputmode' => 'decimal',
                    'step' => '0.01',
                    'min' => '0',
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $skill = $event->getData();

            if (!$skill instanceof Skills) {
                return;
            }

            $form = $event->getForm();
            $name = trim((string) $form->get('newSubjectName')->getData());
            $description = trim((string) $form->get('newSubjectDescription')->getData());
            $coefficient = $form->get('newSubjectCoefficient')->getData();

            $hasNewSubjectData = $name !== ''
                || $description !== ''
                || $coefficient !== null;

            if ($hasNewSubjectData) {
                $hasErrors = false;

                if ($name === '') {
                    $form->get('newSubjectName')->addError(new FormError('Le nom de la matière est obligatoire.'));
                    $hasErrors = true;
                }

                if ($description === '') {
                    $form->get('newSubjectDescription')->addError(new FormError('La description de la matière est obligatoire.'));
                    $hasErrors = true;
                }

                if ($coefficient === null) {
                    $form->get('newSubjectCoefficient')->addError(new FormError('Le coefficient de la matière est obligatoire.'));
                    $hasErrors = true;
                }

                if (!$hasErrors) {
                    $existingSubject = $this->subjectsRepository->findOneByNormalizedName($name);

                    if ($existingSubject instanceof Subjects) {
                        $skill->addSubject($existingSubject);
                    } else {
                        $newSubject = (new Subjects())
                            ->setName($name)
                            ->setDescription($description)
                            ->setCoefficient((float) $coefficient);

                        $skill->addSubject($newSubject);
                    }
                }
            }

            $selectedTypeKeys = [];
            $weightTotal = 0;
            $hasEntries = false;

            foreach ($form->get('gradeTypes') as $entryForm) {
                $type = $entryForm->get('type')->getData();
                $weight = $entryForm->get('weight')->getData();

                if (!$type instanceof GradeTypeNames) {
                    continue;
                }

                $hasEntries = true;
                $typeId = $type->getId();
                $typeKey = $typeId !== null
                    ? sprintf('id_%d', $typeId)
                    : mb_strtolower(trim((string) $type->getName()));

                if (isset($selectedTypeKeys[$typeKey])) {
                    $form->get('gradeTypes')->addError(new FormError('Un type d\'épreuve ne peut être défini qu\'une seule fois par compétence.'));
                    return;
                }

                $selectedTypeKeys[$typeKey] = true;
                $weightTotal += (int) ($weight ?? 0);
            }

            if ($hasEntries && $weightTotal !== 100) {
                $form->get('gradeTypes')->addError(new FormError('La somme des poids doit être égale à 100%.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Skills::class,
            'csrf_protection' => true, // Hérité du formulaire parent
        ]);
    }
}
