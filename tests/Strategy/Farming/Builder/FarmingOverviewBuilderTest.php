<?php

namespace App\Tests\Strategy\Farming\Builder;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Strategy\Farming\Builder\FarmingOverviewBuilder;
use App\Strategy\Farming\Entity\StrategyFarming;
use App\Strategy\Farming\Repository\StrategyFarmingRepository;
use App\Strategy\Lp\Entity\StrategyLp;
use App\Strategy\Lp\Repository\StrategyLpRepository;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class FarmingOverviewBuilderTest extends TestCase
{
    public function testYearlyRewardsInUsd(): void
    {
        $user = new User();
        $eth = new Cryptocurrency()->setPriceUsd(2000);
        $usdc = new Cryptocurrency()->setPriceUsd(1);
        $crypto = new StrategyFarming($user)->setCoin($eth)->setNbCoins(2)->setApr(5);
        $stable = new StrategyFarming($user)->setCoin($usdc)->setNbCoins(1000)->setApr(10);
        $lp = new StrategyLp($user)->setCoin1($eth)->setNbCoin1(1)->setPriceCoin1(2000)
            ->setCoin2($usdc)->setNbCoin2(2000)->setPriceCoin2(1)->setApr(10);

        $farmingRepository = $this->createMock(StrategyFarmingRepository::class);
        $farmingRepository->expects(self::exactly(2))->method('findByOwner')
            ->willReturnCallback(static fn (User $owner, bool $stableCoins): array => $stableCoins ? [$stable] : [$crypto]);
        $lpRepository = $this->createMock(StrategyLpRepository::class);
        $lpRepository->expects(self::once())->method('findByOwner')->with($user)->willReturn([$lp]);

        $overview = new FarmingOverviewBuilder($farmingRepository, $lpRepository)->build($user);

        self::assertSame([$crypto], $overview->cryptoFarmings);
        self::assertSame([$stable], $overview->stableFarmings);
        self::assertSame([$lp], $overview->lps);
        self::assertSame(200.0, $overview->cryptoYearlyUsd);
        self::assertSame(100.0, $overview->stableYearlyUsd);
        self::assertSame(400.0, $overview->lpYearlyUsd);
        self::assertSame(700.0, $overview->totalYearlyUsd());
    }
}
