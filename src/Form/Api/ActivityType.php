<?php

namespace App\Form\Api;

use App\Entity\Activity;
use App\Entity\Exercise;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
            ])->add('exercises', CollectionType::class, [
                'entry_type' => ApiExerciseType::class,
                'mapped' => false,
                'allow_add' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
            'csrf_protection' => false,
            'validation_groups' => ['Default'],
        ]);
    }
}
