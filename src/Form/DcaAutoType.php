<?php

namespace App\Form;

use App\Entity\Cryptocurrency;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DcaAutoType extends AbstractType
{

    public static string $HOURLY = 'h';
    public static string $DAILY = 'd';
    public static string $WEEKLY = 'w';
    public static string $MONTHLY = 'm';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('coin', EntityType::class, [
                'class' => Cryptocurrency::class,
                'placeholder' => 'Choisir une crypto',
                'required' => true,
                'attr' => ['class' => 'js-select2'],
                'mapped' => false,
            ])
            ->add('value', IntegerType::class, [
                'required' => true,
            ])
            ->add('type_recurr', ChoiceType::class, [
                'choices' => [
                    '---' => '',
                    '4 Heures' => self::$HOURLY,
                    'Journalier' => self::$DAILY,
                    'Hebdomadaire' => self::$WEEKLY,
                    'Mensuel' => self::$MONTHLY
                ],
                'required' => true,
            ])
            ->add('nb_recurr', IntegerType::class, [
                'required' => true,
            ])
            ->add('date_first', DateTimeType::class, [
                'required' => true,
                'widget' => 'single_text',

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
