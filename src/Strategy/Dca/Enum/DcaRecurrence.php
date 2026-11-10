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

    public function interval(): \DateInterval
    {
        return new \DateInterval(match ($this) {
            self::FourHours => 'PT4H',
            self::Daily => 'P1D',
            self::Weekly => 'P1W',
            self::Monthly => 'P1M',
        });
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
