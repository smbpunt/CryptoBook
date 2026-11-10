<?php

namespace App\Service;

use App\Strategy\Farming\Repository\StrategyFarmingRepository;
use App\Strategy\Lp\Repository\StrategyLpRepository;
use App\User\Entity\User;

final readonly class FarmingService
{
    public function __construct(
        private StrategyFarmingRepository $strategyFarmingRepository,
        private StrategyLpRepository $strategyLpRepository,
    ) {
    }

    /**
     * Yearly rewards of the single asset farmings, in USD.
     *
     * @param bool|null $isStable only stable (true) or volatile (false) coins, every coin when null
     */
    public function getTotalFarmingSimpleByYearUsd(User $user, ?bool $isStable = null): float
    {
        $strategies = null === $isStable
            ? $this->strategyFarmingRepository->findBy(['owner' => $user])
            : $this->strategyFarmingRepository->findByStable($user, $isStable);

        $total = 0;
        foreach ($strategies as $strategy) {
            $valueUsd = $strategy->getCoin()->getPriceUsd() * $strategy->getNbCoins();
            $total += $strategy->getApr() * $valueUsd / 100;
        }

        return $total;
    }

    /**
     * Yearly rewards of the liquidity pools, in USD.
     */
    public function getTotalFarmingLpByYearUsd(User $user): float
    {
        $strategies = $this->strategyLpRepository->findBy(['owner' => $user]);

        $total = 0;
        foreach ($strategies as $strategy) {
            $valueUsd = $strategy->getCoin1()->getPriceUsd() * $strategy->getNbCoin1() + $strategy->getCoin2()->getPriceUsd() * $strategy->getNbCoin2();
            $total += $strategy->getApr() * $valueUsd / 100;
        }

        return $total;
    }

    /**
     * Yearly rewards of every farming (single asset and liquidity pools), in USD.
     */
    public function getTotalFarmingByYearUsd(User $user): float
    {
        return $this->getTotalFarmingSimpleByYearUsd($user) + $this->getTotalFarmingLpByYearUsd($user);
    }
}
