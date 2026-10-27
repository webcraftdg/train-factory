<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Exercise;
use App\Repository\ExerciseRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'nom'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('objective', TextareaType::class, ['label' => 'Objectif', 'required' => false])
            ->add('instruction', TextareaType::class, ['label' => 'Consigne', 'required' => false])
            ->add('duration', TextType::class, ['label' => 'Durée'])
            ->add('hydration', CheckboxType::class, [
                'label' => 'Ajouter l\'hydratation',
                'required' => false
            ])->add('exerciseOrder', HiddenType::class, [
                'mapped' => false,
            ])->add('exercises', EntityType::class, [
                'class' => Exercise::class,
                'label'  => 'choix des exercices',
                'choice_label' => fn (Exercise $exercise): string => (string) $exercise,
                'query_builder' => function (ExerciseRepository $repository) {
                        return $repository->createQueryFindWithConditions();
                },
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
