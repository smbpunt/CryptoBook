<?php

namespace App\Position\Form;

use App\Position\Entity\Vente;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Vente>
 */
class VenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('soldAt', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'input' => 'datetime_immutable',
                'default_today' => true,
            ])
            ->add('percent')
            ->add('priceSold');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vente::class,
        ]);
    }
}
