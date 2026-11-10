<?php

namespace App\Service;

use App\Deposit\Repository\DepositRepository;
use App\Entity\User;

final readonly class DepositService
{
    public function __construct(private DepositRepository $depositRepository)
    {
    }

    /**
     * Total amount deposited by the user, in USD.
     */
    public function getTotalDepositUsd(User $user): float
    {
        $total = 0;
        foreach ($this->depositRepository->findByOwnerWithCurrency($user) as $deposit) {
            $total += $deposit->getAmountUsd();
        }

        return $total;
    }
}
