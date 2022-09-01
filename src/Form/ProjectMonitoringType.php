<?php

namespace App\Form;

use App\Entity\Cryptocurrency;
use App\Entity\ProjectMonitoring;
use App\Entity\TypeProject;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectMonitoringType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => ''
            ])
            ->add('note', TextareaType::class, [
                'required' => false,
                'empty_data' => ''
            ])
            ->add('links', CollectionType::class, [
                'entry_type' => TextType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
            ])
            ->add('coin', EntityType::class, [
                'class' => Cryptocurrency::class,
                'required' => false,
                'attr' => ['class' => 'js-select2'],
                'placeholder' => 'Choisir une crypto',
            ])
            ->add('type', EntityType::class, [
                'class' => TypeProject::class,
                'required' => true,
                'attr' => ['class' => 'js-select2'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectMonitoring::class,
        ]);
    }
}
