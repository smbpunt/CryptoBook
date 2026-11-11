<?php

namespace App\ProjectMonitoring\Form;

use App\Cryptocurrency\Form\CryptocurrencyAutocompleteField;
use App\ProjectMonitoring\Entity\ProjectMonitoring;
use App\ProjectMonitoring\Entity\TypeProject;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ProjectMonitoring>
 */
class ProjectMonitoringType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('note', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('links', CollectionType::class, [
                'entry_type' => TextType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
            ])
            ->add('coin', CryptocurrencyAutocompleteField::class, [
                'required' => false,
            ])
            ->add('type', EntityType::class, [
                'class' => TypeProject::class,
                'required' => true,
                'autocomplete' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectMonitoring::class,
        ]);
    }
}
