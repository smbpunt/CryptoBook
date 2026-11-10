<?php

namespace App\Position\Dto;

/**
 * Planned sales of a position and what they bring back.
 */
final readonly class ExitPlan
{
    /**
     * @param list<ExitPlanStep> $steps
     * @param float|null         $resultPercent null when the position cost nothing
     */
    public function __construct(
        public array $steps,
        public float $remainingCoins,
        public float $remainingPercent,
        public float $recoveredUsd,
        public float $resultUsd,
        public ?float $resultPercent,
    ) {
    }
}
