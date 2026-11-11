<?php

namespace App\Strategy\Dca\Form;

use App\Cryptocurrency\Form\CryptocurrencyAutocompleteField;
use App\Strategy\Dca\Dto\DcaAutoQuery;
use App\Strategy\Dca\Enum\DcaRecurrence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class DcaAutoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('coin', CryptocurrencyAutocompleteField::class, [
                'required' => true,
                'mapped' => false,
            ])
            ->add('value', IntegerType::class, [
                'required' => true,
            ])
            ->add('type_recurr', EnumType::class, [
                'class' => DcaRecurrence::class,
                'choice_label' => static fn (DcaRecurrence $recurrence): string => $recurrence->label(),
                'placeholder' => '---',
                'required' => true,
            ])
            ->add('nb_recurr', IntegerType::class, [
                'required' => true,
                'attr' => ['min' => 1, 'max' => DcaAutoQuery::MAX_COUNT],
            ])
            ->add('date_first', DateTimeType::class, [
                'required' => true,
                'widget' => 'single_text',
            ]);
    }
}
