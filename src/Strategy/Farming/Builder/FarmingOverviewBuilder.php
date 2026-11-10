<?php

namespace App\Strategy\Farming\Builder;

use App\Strategy\Farming\Dto\FarmingOverview;
use App\Strategy\Farming\Entity\StrategyFarming;
use App\Strategy\Farming\Repository\StrategyFarmingRepository;
use App\Strategy\Lp\Entity\StrategyLp;
use App\Strategy\Lp\Repository\StrategyLpRepository;
use App\User\Entity\User;

final readonly class FarmingOverviewBuilder
{
    public function __construct(
        private StrategyFarmingRepository $strategyFarmingRepository,
        private StrategyLpRepository $strategyLpRepository,
    ) {
    }

    public function build(User $user): FarmingOverview
    {
        $crypto = $this->strategyFarmingRepository->findByOwner($user, stable: false);
        $stable = $this->strategyFarmingRepository->findByOwner($user, stable: true);
        $lps = $this->strategyLpRepository->findByOwner($user);

        return new FarmingOverview(
            $crypto,
            $stable,
            $lps,
            self::sum(array_map(static fn (StrategyFarming $farming): float => $farming->getAnnualRewardsUsd(), $crypto)),
            self::sum(array_map(static fn (StrategyFarming $farming): float => $farming->getAnnualRewardsUsd(), $stable)),
            // At the current prices of the coins.
            self::sum(array_map(static fn (StrategyLp $lp): float => $lp->getApr() * ($lp->getCoin1()->getPriceUsd() * $lp->getNbCoin1() + $lp->getCoin2()->getPriceUsd() * $lp->getNbCoin2()) / 100, $lps)),
        );
    }

    /**
     * @param list<float> $values
     */
    private static function sum(array $values): float
    {
        return (float) array_sum($values);
    }
}
