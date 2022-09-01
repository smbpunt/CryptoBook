<?php

namespace App\Form;

use App\Entity\Blockchain;
use App\Entity\Cryptocurrency;
use App\Entity\Dapp;
use App\Entity\Loan;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nbCoins')
            ->add('loanedAt', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datetimepicker'],
                'required' => false,
                'format' => 'dd/MM/yyyy',
                'input' => 'datetime_immutable',
            ])
            ->add('coin', EntityType::class, [
                'class' => Cryptocurrency::class,
                'required' => true,
                'attr' => ['class' => 'js-select2'],
                'placeholder' => 'Choisir une crypto',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => ''
            ])
            ->add('blockchain', EntityType::class, [
                'required' => false,
                'class' => Blockchain::class,
                'attr' => ['class' => 'js-select2'],
            ]);

        $formModifier = static function (FormInterface $form, Blockchain $blockchain = null) {
            $dapps = null === $blockchain ? [] : $blockchain->getDapps();
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
            function (FormEvent $event) use ($formModifier) {
                /**
                 * @var Loan $data
                 */
                $data = $event->getData();
                $blockchain = $data->getDapp()?->getBlockchain();
                $formModifier($event->getForm(), $blockchain);
            }
        );

        $builder->get('blockchain')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $blockchain = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $blockchain);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Loan::class,
        ]);
    }
}
