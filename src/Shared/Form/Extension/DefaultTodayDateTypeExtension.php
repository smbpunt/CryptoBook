<?php

namespace App\Shared\Form\Extension;

use Psr\Clock\ClockInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * `default_today`: an empty date of a new object (no id yet) proposes today, by the application clock (UTC).
 * The field stays optional, and an edit form keeps the stored date, even an empty one.
 */
final class DefaultTodayDateTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public static function getExtendedTypes(): iterable
    {
        return [DateType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('default_today', false);
        $resolver->setAllowedTypes('default_today', 'bool');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['default_today']) {
            return;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            if (null !== $event->getData()) {
                return;
            }

            // The object is set on the parent before its fields; a collection prototype has none.
            $object = $event->getForm()->getParent()?->getData();
            if (null === $object || (\is_object($object) && method_exists($object, 'getId') && null === $object->getId())) {
                $event->setData($this->clock->now()->setTime(0, 0));
            }
        });
    }
}
