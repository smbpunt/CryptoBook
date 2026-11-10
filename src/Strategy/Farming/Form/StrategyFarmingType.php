<?php

namespace App\Strategy\Farming\Form;

use App\Blockchain\Entity\Blockchain;
use App\Blockchain\Entity\Dapp;
use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Strategy\Farming\Entity\StrategyFarming;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrategyFarmingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enteredAt', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'input' => 'datetime_immutable',
            ])
            ->add('nbCoins')
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('apr')
            ->add('coin', EntityType::class, [
                'required' => true,
                'placeholder' => 'Choisir une crypto',
                'class' => Cryptocurrency::class,
            ])
            ->add('blockchain', EntityType::class, [
                'required' => false,
                'class' => Blockchain::class,
                'attr' => ['class' => 'js-select2'],
            ]);

        $formModifier = static function (FormInterface $form, ?Blockchain $blockchain = null): void {
            $dapps = $blockchain?->getDapps() ?? [];
            $form->add('dapp', EntityType::class, [
                'class' => Dapp::class,
                'required' => true,
                'choices' => $dapps,
                'placeholder' => 'Veuillez choisir une blockchain',
                'attr' => ['class' => 'js-select2'],
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function (FormEvent $event) use ($formModifier): void {
                /**
                 * @var StrategyFarming $data
                 */
                $data = $event->getData();
                $blockchain = $data->getDapp()?->getBlockchain();
                $formModifier($event->getForm(), $blockchain);
            }
        );

        $builder->get('blockchain')->addEventListener(
            FormEvents::POST_SUBMIT,
            static function (FormEvent $event) use ($formModifier): void {
                $blockchain = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $blockchain);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StrategyFarming::class,
        ]);
    }
}
