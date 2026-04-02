<?php

namespace App\Form;

use App\Entity\Skills;
use App\Entity\Subjects;
use App\Repository\SubjectsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
                'label' => 'Matieres existantes',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                ],
            ])

            ->add('newSubjectName', TextType::class, [
                'label' => 'Nouvelle matiere',
                'mapped' => false,
                'required' => false,
            ])
            ->add('newSubjectDescription', TextareaType::class, [
                'label' => 'Description de la nouvelle matiere',
                'mapped' => false,
                'required' => false,
                'empty_data' => '',
            ])
            ->add('newSubjectCoefficient', NumberType::class, [
                'label' => 'Coefficient de la nouvelle matiere',
                'mapped' => false,
                'required' => false,
                'scale' => 2,
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            $skill = $event->getData();

            if (!$skill instanceof Skills) {
                return;
            }

            $form = $event->getForm();
            $name = trim((string) $form->get('newSubjectName')->getData());
            if ($name === '') {
                return;
            }

            $existingSubject = $this->subjectsRepository->findOneBy(['name' => $name]);

            if ($existingSubject instanceof Subjects) {
                $skill->addSubject($existingSubject);

                return;
            }

            $newSubject = (new Subjects())
                ->setName($name)
                ->setDescription((string) $form->get('newSubjectDescription')->getData())
                ->setCoefficient((float) ($form->get('newSubjectCoefficient')->getData() ?? 0));

            $skill->addSubject($newSubject);
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
