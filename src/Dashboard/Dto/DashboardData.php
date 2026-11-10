<?php

namespace App\Dashboard\Dto;

/**
 * Everything the dashboard shows for a user. Scalars only: it is cached.
 */
final readonly class DashboardData
{
    public float $totalUsd;

    /**
     * @param list<DashboardHolding> $cryptoHoldings
     * @param list<DashboardHolding> $stableHoldings
     */
    public function __construct(
        public array $cryptoHoldings,
        public array $stableHoldings,
        public float $cryptoUsd,
        public float $stableUsd,
        public float $depositUsd,
        public float $loanUsd,
        public float $yearFarmingUsd,
        public string $currencySymbol,
        /** Value of one unit of the favorite currency, in USD. */
        public float $usdPerFavorite,
        /** Value of one USD, in the favorite currency. */
        public float $favoritePerUsd,
    ) {
        $this->totalUsd = $cryptoUsd + $stableUsd;
    }

    public function toFavorite(float $amountUsd): float
    {
        return $amountUsd * $this->favoritePerUsd;
    }
}
