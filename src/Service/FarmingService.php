<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\StrategyFarmingRepository;
use App\Repository\StrategyLpRepository;
use Symfony\Bundle\SecurityBundle\Security;

class FarmingService
{
    private User $user;

    /**
     * @param StrategyFarmingRepository $strategyFarmingRepository
     * @param StrategyLpRepository $strategyLpRepository
     * @param Security $security
     */
    public function __construct(
        private readonly StrategyFarmingRepository $strategyFarmingRepository,
        private readonly StrategyLpRepository $strategyLpRepository,
        Security $security
    )
    {
        $this->user = $security->getUser();
    }


    /**
     * @return float
     * Retourne le total du farming (single asset) à l'année en USD
     */
    public function getTotalFarmingSimpleByYearUsd(bool $checkBooleanIsStable = false, bool $isStable = false): float
    {
        $strategies = $checkBooleanIsStable ? $this->strategyFarmingRepository->findByStable($this->user, $isStable) : $this->strategyFarmingRepository->findBy([
            'owner' => $this->user
        ]);

        $totalYearFarmingUsd = 0;
        foreach ($strategies as $strategy) {
            $value_dollar = $strategy->getCoin()->getPriceUsd() * $strategy->getNbCoins();
            $totalYearFarmingUsd += $strategy->getApr() * $value_dollar / 100;
        }

        return $totalYearFarmingUsd;
    }


    /**
     * @return float
     * Retourne le total du farming (LP) à l'année en USD
     */
    public function getTotalFarmingLpByYearUsd(): float
    {
        $strategies = $this->strategyLpRepository->findBy([
            'owner' => $this->user
        ]);

        $totalYearFarmingUsd = 0;
        foreach ($strategies as $strategy) {
            $value_dollar = $strategy->getCoin1()->getPriceUsd() * $strategy->getNbCoin1() + $strategy->getCoin2()->getPriceUsd() * $strategy->getNbCoin2();
            $totalYearFarmingUsd += $strategy->getApr() * $value_dollar / 100;
        }

        return $totalYearFarmingUsd;
    }

    /**
     * @return float
     * Retourne le total du farming (Single asset + LP) à l'année en USD
     */
    public function getTotalFarmingByYearUsd(): float
    {
        return $this->getTotalFarmingSimpleByYearUsd() + $this->getTotalFarmingLpByYearUsd();
    }
}