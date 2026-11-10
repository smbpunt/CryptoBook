<?php

namespace App\Deposit\Calculator;

use App\Deposit\Dto\DepositStep;
use App\Deposit\Entity\Deposit;

/**
 * Cumulated deposits over time, in USD at today's rate (as the total "Investi").
 */
final readonly class DepositTimelineCalculator
{
    /**
     * One step per deposit date, oldest first. Deposits without a date count from the beginning.
     *
     * @param iterable<Deposit> $deposits
     *
     * @return list<DepositStep>
     */
    public function steps(iterable $deposits): array
    {
        $undatedUsd = 0.0;
        $usdByDay = [];
        foreach ($deposits as $deposit) {
            $day = $deposit->getDepositedAt()?->format('Y-m-d');
            if (null === $day) {
                $undatedUsd += $deposit->getAmountUsd();
            } else {
                $usdByDay[$day] = ($usdByDay[$day] ?? 0.0) + $deposit->getAmountUsd();
            }
        }
        ksort($usdByDay);

        $steps = $undatedUsd > 0 ? [new DepositStep(null, $undatedUsd)] : [];
        $totalUsd = $undatedUsd;
        foreach ($usdByDay as $day => $usd) {
            $totalUsd += $usd;
            $steps[] = new DepositStep(new \DateTimeImmutable($day, new \DateTimeZone('UTC')), $totalUsd);
        }

        return $steps;
    }

    /**
     * Total deposited at a given time.
     *
     * @param list<DepositStep> $steps
     */
    public function totalAt(array $steps, \DateTimeImmutable $at): float
    {
        $totalUsd = 0.0;
        foreach ($steps as $step) {
            if (null !== $step->from && $step->from > $at) {
                break;
            }
            $totalUsd = $step->totalUsd;
        }

        return $totalUsd;
    }
}
