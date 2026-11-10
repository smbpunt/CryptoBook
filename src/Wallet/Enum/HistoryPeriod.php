<?php

namespace App\Wallet\Enum;

/**
 * Periods of the wallet history chart. Hourly snapshots up to 7 days, one per day beyond.
 */
enum HistoryPeriod: string
{
    case Day = '24h';
    case Week = '7d';
    case Month = '30d';
    case Quarter = '90d';
    case Year = '1y';
    case All = 'all';

    /**
     * Start of the period, null for the whole history.
     */
    public function since(\DateTimeImmutable $now): ?\DateTimeImmutable
    {
        return match ($this) {
            self::Day => $now->modify('-24 hours'),
            self::Week => $now->modify('-7 days'),
            self::Month => $now->modify('-30 days'),
            self::Quarter => $now->modify('-90 days'),
            self::Year => $now->modify('-1 year'),
            self::All => null,
        };
    }

    public function isHourly(): bool
    {
        return match ($this) {
            self::Day, self::Week => true,
            default => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Day => '24 h',
            self::Week => '7 j',
            self::Month => '30 j',
            self::Quarter => '90 j',
            self::Year => '1 an',
            self::All => 'Tout',
        };
    }

    /**
     * End of the sentence "variation …", when the history covers the whole period.
     */
    public function description(): string
    {
        return match ($this) {
            self::Day => 'sur 24 heures',
            self::Week => 'sur 7 jours',
            self::Month => 'sur 30 jours',
            self::Quarter => 'sur 90 jours',
            self::Year => 'sur 1 an',
            self::All => 'depuis le début',
        };
    }
}
