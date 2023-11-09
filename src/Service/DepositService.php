<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\DepositRepository;
use Symfony\Bundle\SecurityBundle\Security;

class DepositService
{

    public function __construct(
        private readonly DepositRepository $depositRepository,
        private readonly Security $security
    )
    {
    }

    /**
     * @param User $user
     * @return float
     * Retourne le solde total d'argent investi pour le User
     */
    public function getTotalDepositUsd(User $user): float
    {
        $deposits = $this->depositRepository->findBy(['owner' => $user]);

        $totalDepositUsd = 0;
        foreach ($deposits as $deposit) {
            $totalDepositUsd += $deposit->getAmountUsd();
        }

        return $totalDepositUsd;
    }

    /**
     * @return float
     * Retourne le solde total d'argent investi pour l'utilisateur connecté
     */
    public function getTotalDepositUsdCurrentUser(): float
    {
        return $this->getTotalDepositUsd($this->security->getUser());
    }
}