<?php

namespace App\Form;

use App\Entity\Cryptocurrency;
use App\Entity\Position;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('openedAt', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datetimepicker'],
                'required' => false,
                'format' => 'dd/MM/yyyy',
                'input' => 'datetime_immutable',
            ])
            ->add('nbCoins', NumberType::class, [
                'required' => true,
                'scale' => 8,
            ])
            ->add('isOpened', CheckboxType::class, [
                'label' => 'Position ouverte ?',
                'required' => false,
            ])
            ->add('isDca', CheckboxType::class, [
                'label' => 'DCA ?',
                'required' => false,
            ])
            ->add('entryCost', NumberType::class, [
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('coin', EntityType::class, [
                'class' => Cryptocurrency::class,
                'required' => true,
                'attr' => ['class' => 'js-select2'],
            ])
            ->add('ventes', CollectionType::class, [
                'entry_type' => VenteType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
            ])
            ->add('strategies', CollectionType::class, [
                'entry_type' => StrategyPositionType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
            ])
            ->add('submit', SubmitType::class)
            ->add('submitAndNext', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Position::class,
        ]);
    }
}
