<?php

namespace App\Form;

use App\Entity\GradeTypeNames;
use App\Entity\GradeTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

class SkillWeightType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EntityType::class, [
                'class' => GradeTypeNames::class,
                'choice_label' => 'name',
                'label' => 'Type d\'épreuve existant',
                'placeholder' => '-- Choisir un type --',
                'required' => false,
            ])
            ->add('weight', IntegerType::class, [
                'label' => 'Poids (%)',
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'max' => 100,
                    'step' => 1,
                ],
                'constraints' => [
                    new NotNull(message: 'Le poids est obligatoire.'),
                    new Range(min: 1, max: 100, notInRangeMessage: 'Le poids doit être entre 1 et 100.'),
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $gradeType = $event->getData();

            if (!$gradeType instanceof GradeTypes) {
                return;
            }

            $form = $event->getForm();
            $selectedType = $gradeType->getType();

            if ($selectedType === null) {
                $form->get('type')->addError(new FormError('Le type d\'épreuve est obligatoire.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GradeTypes::class,
        ]);
    }
}
