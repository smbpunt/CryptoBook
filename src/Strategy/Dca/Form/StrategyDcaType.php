<?php

namespace App\Strategy\Dca\Form;

use App\Strategy\Dca\Entity\StrategyDca;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<StrategyDca>
 */
class StrategyDcaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fiatToDcaEur')
            ->add('farmingToDcaUsd')
            ->add('parts', CollectionType::class, [
                'entry_type' => CoinPercentDcaType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                // Keeps the "sum to 100 %" error next to the parts.
                'error_bubbling' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StrategyDca::class,
        ]);
    }
}
