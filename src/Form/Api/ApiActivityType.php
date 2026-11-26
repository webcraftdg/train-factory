<?php

namespace App\Form\Api;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', IntegerType::class, [  'required' => false,])
            ->add('name', TextType::class, [])
            ->add('description', TextareaType::class, [ 'required' => false])
            ->add('objective', TextareaType::class, ['required' => false])
            ->add('instruction', TextareaType::class, ['required' => false])
            ->add('duration', TextType::class, ['label' => 'Durée'])
            ->add('hydration', CheckboxType::class, [
                'required' => false
            ])->add('separatorAfter', CheckboxType::class, [
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
            'csrf_protection' => false,
            'validation_groups' => ['Default'],
            'extra_fields_message' => 'Champs non autorisés : {{ extra_fields }}.',
        ]);
    }
}
