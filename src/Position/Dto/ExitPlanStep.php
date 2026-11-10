<?php

namespace App\Position\Dto;

use App\Position\Entity\StrategyPosition;

/**
 * One planned sale of an exit plan.
 */
final readonly class ExitPlanStep
{
    public function __construct(
        public StrategyPosition $sale,
        public float $soldCoins,
        public float $recoveredUsd,
        public float $remainingCoins,
    ) {
    }
}
