<?php

namespace App\Tests\Unit\Service;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Entity\StrategyFarming;
use App\Entity\StrategyLp;
use App\Entity\User;
use App\Repository\StrategyFarmingRepository;
use App\Repository\StrategyLpRepository;
use App\Service\FarmingService;
use PHPUnit\Framework\TestCase;

final class FarmingServiceTest extends TestCase
{
    public function testYearlyRewardsInUsd(): void
    {
        $user = new User();
        $eth = new Cryptocurrency()->setPriceUsd(2000);
        $usdc = new Cryptocurrency()->setPriceUsd(1);

        $farmingRepository = $this->createMock(StrategyFarmingRepository::class);
        $farmingRepository->method('findBy')->with(['owner' => $user])->willReturn([
            new StrategyFarming($user)->setCoin($eth)->setNbCoins(2)->setApr(5),
        ]);
        $farmingRepository->expects(self::once())->method('findByStable')->with($user, true)->willReturn([
            new StrategyFarming($user)->setCoin($usdc)->setNbCoins(1000)->setApr(10),
        ]);
        $lpRepository = $this->createStub(StrategyLpRepository::class);
        $lpRepository->method('findBy')->willReturn([
            new StrategyLp($user)->setCoin1($eth)->setNbCoin1(1)->setCoin2($usdc)->setNbCoin2(2000)->setApr(10),
        ]);
        $service = new FarmingService($farmingRepository, $lpRepository);

        self::assertSame(200.0, $service->getTotalFarmingSimpleByYearUsd($user));
        self::assertSame(100.0, $service->getTotalFarmingSimpleByYearUsd($user, isStable: true));
        self::assertSame(400.0, $service->getTotalFarmingLpByYearUsd($user));
        self::assertSame(600.0, $service->getTotalFarmingByYearUsd($user));
    }
}
