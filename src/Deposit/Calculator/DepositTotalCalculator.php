<?php

namespace App\Deposit\Calculator;

use App\Deposit\Entity\Deposit;

final readonly class DepositTotalCalculator
{
    /**
     * Total amount of the deposits, in USD.
     *
     * @param iterable<Deposit> $deposits
     */
    public function totalUsd(iterable $deposits): float
    {
        $total = 0.0;
        foreach ($deposits as $deposit) {
            $total += $deposit->getAmountUsd();
        }

        return $total;
    }
}
