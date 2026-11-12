<?php

namespace App\Dashboard\Dto;

use App\Wallet\Enum\HistoryPeriod;

/**
 * Wallet value over a period, ending on the current total of the dashboard.
 */
final readonly class WalletHistory
{
    /**
     * @param list<WalletHistoryPoint> $points oldest first, empty below 2 points
     */
    public function __construct(
        public HistoryPeriod $period,
        public array $points = [],
        public float $changeUsd = 0.0,
        /** Null when the series starts at 0. */
        public ?float $changePercent = null,
        /** False when the history starts after the beginning of the period. */
        public bool $coversPeriod = true,
    ) {
    }

    public function isEmpty(): bool
    {
        return [] === $this->points;
    }

    public function hasDeposits(): bool
    {
        return array_any($this->points, static fn ($point) => $point->investedUsd > 0);
    }

    public function startedAt(): ?\DateTimeImmutable
    {
        return $this->points[0]->at ?? null;
    }
}
