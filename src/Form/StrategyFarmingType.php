<?php

namespace App\Form;

use App\Entity\Dapp;
use App\Entity\StrategyFarming;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrategyFarmingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nbCoins')
            ->add('apr')
            ->add('coin')
            ->add('dapp', EntityType::class, [
                'class' => Dapp::class,
                'required' => true,
                'attr' => ['class' => 'js-select2'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StrategyFarming::class,
        ]);
    }
}
