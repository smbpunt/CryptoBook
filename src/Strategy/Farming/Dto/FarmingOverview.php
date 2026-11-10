<?php

namespace App\Strategy\Farming\Dto;

use App\Strategy\Farming\Entity\StrategyFarming;
use App\Strategy\Lp\Entity\StrategyLp;

/**
 * Farmings of a user and their yearly rewards, in USD.
 */
final readonly class FarmingOverview
{
    /**
     * @param list<StrategyFarming> $cryptoFarmings
     * @param list<StrategyFarming> $stableFarmings
     * @param list<StrategyLp>      $lps
     */
    public function __construct(
        public array $cryptoFarmings,
        public array $stableFarmings,
        public array $lps,
        public float $cryptoYearlyUsd,
        public float $stableYearlyUsd,
        public float $lpYearlyUsd,
    ) {
    }

    public function totalYearlyUsd(): float
    {
        return $this->cryptoYearlyUsd + $this->stableYearlyUsd + $this->lpYearlyUsd;
    }
}
