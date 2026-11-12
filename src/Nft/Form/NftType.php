<?php

namespace App\Nft\Form;

use App\Blockchain\Entity\Blockchain;
use App\Cryptocurrency\Form\CryptocurrencyAutocompleteField;
use App\Nft\Entity\Nft;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Nft>
 */
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
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('purchasedOn', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'input' => 'datetime_immutable',
            ])
            ->add('soldOn', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'input' => 'datetime_immutable',
            ])
            ->add('priceSoldCrypto')
            ->add('priceSoldUsd')
            ->add('percentSaleFees')
            ->add('blockchain', EntityType::class, [
                'class' => Blockchain::class,
                'required' => true,
                'autocomplete' => true,
            ])
            ->add('cryptocurrency', CryptocurrencyAutocompleteField::class, [
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Nft::class,
        ]);
    }
}
