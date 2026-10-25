<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Block;
use App\Entity\Session;
use App\Repository\ActivityRepository;
use App\Repository\BlockRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('description', TextareaType::class, ['label' => 'Description'])
            ->add('objective', TextareaType::class, ['label' => 'Objectif'])
            ->add('instruction', TextareaType::class, ['label' => 'Consigne'])
            ->add('activities', EntityType::class, [
                'class' => Activity::class,
                'label'  => 'Choix des activités',
                'choice_label' => fn (Activity $activity): string => (string) $activity,
                'query_builder' => function (ActivityRepository $repository) {
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
            'data_class' => Block::class,
        ]);
    }
}
