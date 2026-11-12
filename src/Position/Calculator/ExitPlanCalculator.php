<?php

namespace App\Position\Calculator;

use App\Position\Dto\ExitPlan;
use App\Position\Dto\ExitPlanStep;
use App\Position\Entity\Position;

final readonly class ExitPlanCalculator
{
    /**
     * Each planned sale sells a percentage of the coins left by the previous ones.
     */
    public function calculate(Position $position): ExitPlan
    {
        $remaining = (float) $position->getNbCoins();
        $recovered = 0.0;
        $steps = [];
        foreach ($position->getStrategies() as $sale) {
            $sold = $remaining * $sale->getPercent() / 100;
            $remaining -= $sold;
            $recovered += $sold * $sale->getPriceSold();
            $steps[] = new ExitPlanStep($sale, $sold, $sold * $sale->getPriceSold(), $remaining);
        }
        $result = $recovered - $position->getEntryCost();

        return new ExitPlan(
            $steps,
            $remaining,
            $remaining * 100 / $position->getNbCoins(),
            $recovered,
            $result,
            $position->getEntryCost() > 0 ? $result * 100 / $position->getEntryCost() : null,
        );
    }
}
