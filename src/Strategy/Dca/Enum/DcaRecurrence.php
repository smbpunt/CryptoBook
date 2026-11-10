<?php

namespace App\Strategy\Dca\Enum;

/**
 * Interval between the purchases of a DCA Auto. The values are the ones of the query string.
 */
enum DcaRecurrence: string
{
    case FourHours = 'h';
    case Daily = 'd';
    case Weekly = 'w';
    case Monthly = 'm';

    /**
     * Date of the purchase number $index (0 for the first one). A monthly purchase keeps the day
     * of the first one, or the last day of a shorter month.
     */
    public function dateAt(\DateTimeImmutable $first, int $index): \DateTimeImmutable
    {
        // Days and weeks on the calendar, so the hour stays the same across a DST change.
        $modifier = match ($this) {
            self::FourHours => \sprintf('+%d hours', 4 * $index),
            self::Daily => \sprintf('+%d days', $index),
            self::Weekly => \sprintf('+%d weeks', $index),
            self::Monthly => null,
        };
        if (null !== $modifier) {
            return $first->modify($modifier);
        }

        $month = $first->modify('first day of this month')->modify(\sprintf('+%d months', $index));
        $day = min((int) $first->format('j'), (int) $month->format('t'));

        return $month->setDate((int) $month->format('Y'), (int) $month->format('n'), $day);
    }

    public function label(): string
    {
        return match ($this) {
            self::FourHours => '4 Heures',
            self::Daily => 'Journalier',
            self::Weekly => 'Hebdomadaire',
            self::Monthly => 'Mensuel',
        };
    }
}
