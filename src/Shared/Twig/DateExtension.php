<?php

namespace App\Shared\Twig;

use Twig\Attribute\AsTwigFilter;

/**
 * French dates of the lists: "10 nov. 2026". The application locale is en.
 */
final class DateExtension
{
    private ?\IntlDateFormatter $formatter = null;

    #[AsTwigFilter('day')]
    public function day(?\DateTimeInterface $date): string
    {
        if (null === $date) {
            return '';
        }

        $this->formatter ??= new \IntlDateFormatter('fr_FR', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, pattern: 'd MMM yyyy');
        $this->formatter->setTimeZone($date->getTimezone());

        return (string) $this->formatter->format($date);
    }
}
