<?php

namespace App\ProjectMonitoring\Form;

use App\Cryptocurrency\Form\CryptocurrencyAutocompleteField;
use App\ProjectMonitoring\Entity\ProjectMonitoring;
use App\ProjectMonitoring\Entity\TypeProject;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ProjectMonitoring>
 */
class ProjectMonitoringType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('note', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('links', CollectionType::class, [
                'entry_type' => TextType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
            ])
            ->add('coin', CryptocurrencyAutocompleteField::class, [
                'required' => false,
            ])
            ->add('type', EntityType::class, [
                'class' => TypeProject::class,
                'required' => true,
                'autocomplete' => true,
            ]);

        // Drops the empty rows and re-indexes the others like setLinks() does: with gaps in the keys (empty or
        // removed rows), the error of a link would show under another row or not at all.
        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();
            if (\is_array($data) && \is_array($data['links'] ?? null)) {
                $data['links'] = array_values(array_filter($data['links'], static fn (mixed $link): bool => '' !== trim((string) $link)));
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectMonitoring::class,
        ]);
    }
}
