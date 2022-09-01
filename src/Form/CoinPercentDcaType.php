<?php

namespace App\Form;

use App\Entity\CoinPercentDca;
use App\Entity\Cryptocurrency;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoinPercentDcaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('percent')
            ->add('coin', EntityType::class, [
                'class' => Cryptocurrency::class,
                'required' => true,
                'attr' => ['class' => 'js-select2'],
                'placeholder' => 'Choisir une crypto',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CoinPercentDca::class,
        ]);
    }
}
