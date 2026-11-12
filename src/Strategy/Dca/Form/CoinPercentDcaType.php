<?php

namespace App\Strategy\Dca\Form;

use App\Cryptocurrency\Form\CryptocurrencyAutocompleteField;
use App\Strategy\Dca\Entity\CoinPercentDca;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<CoinPercentDca>
 */
class CoinPercentDcaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('percent')
            ->add('coin', CryptocurrencyAutocompleteField::class, [
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CoinPercentDca::class,
        ]);
    }
}
