<?php

namespace App\Form;

use App\Entity\Blockchain;
use App\Entity\Cryptocurrency;
use App\Entity\Nft;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('collection')
            ->add('num')
            ->add('rank')
            ->add('supply')
            ->add('priceCrypto')
            ->add('priceUsd')
            ->add('description')
            ->add('purchasedOn', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datetimepicker'],
                'required' => false,
                'format' => 'dd/MM/yyyy',
                'input' => 'datetime_immutable',
            ])
            ->add('soldOn', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datetimepicker'],
                'required' => false,
                'format' => 'dd/MM/yyyy',
                'input' => 'datetime_immutable',
            ])
            ->add('priceSoldCrypto')
            ->add('priceSoldUsd')
            ->add('percentSaleFees')
            ->add('blockchain', EntityType::class, [
                'class' => Blockchain::class,
                'required' => true,
                'attr' => ['class' => 'js-select2'],
            ])
            ->add('cryptocurrency', EntityType::class, [
                'class' => Cryptocurrency::class,
                'required' => true,
                'attr' => ['class' => 'js-select2'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Nft::class,
        ]);
    }
}
