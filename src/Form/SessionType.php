<?php

namespace App\Form;

use App\Entity\Block;
use App\Entity\Session;
use App\Repository\BlockRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('description', TextareaType::class, ['label' => 'Description'])
            ->add('target', TextType::class, ['label' => 'Cible de la séance'])
            ->add('blocks', EntityType::class, [
                'class' => Block::class,
                'label'  => 'Choix des blocs',
                'choice_label' => fn (Block $block): string => (string) $block,
                'query_builder' => function (BlockRepository $repository) {
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
            'data_class' => Session::class,
        ]);
    }
}
