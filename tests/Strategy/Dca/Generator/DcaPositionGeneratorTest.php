<?php

namespace App\Tests\Strategy\Dca\Generator;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Strategy\Dca\Entity\CoinPercentDca;
use App\Strategy\Dca\Entity\StrategyDca;
use App\Strategy\Dca\Generator\DcaPositionGenerator;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class DcaPositionGeneratorTest extends TestCase
{
    public function testSplitsTheAmountByPercent(): void
    {
        $user = new User();
        $btc = new Cryptocurrency()->setPriceUsd(50000);
        $eth = new Cryptocurrency()->setPriceUsd(3000);
        $dca = new StrategyDca($user)
            ->addPart(new CoinPercentDca()->setCoin($btc)->setPercent(60))
            ->addPart(new CoinPercentDca()->setCoin($eth)->setPercent(40));

        $positions = new DcaPositionGenerator(new MockClock('2024-05-01 12:34:56'))->generate($dca, $user, 1000);

        self::assertCount(2, $positions);
        [$p1, $p2] = $positions;
        self::assertSame($btc, $p1->getCoin());
        self::assertSame(600.0, $p1->getEntryCost());
        self::assertSame(0.012, $p1->getNbCoins());
        self::assertSame('DCA 60%', $p1->getDescription());
        self::assertSame(400.0, $p2->getEntryCost());
        self::assertSame(0.13333333, $p2->getNbCoins());
        foreach ($positions as $position) {
            self::assertSame($user, $position->getOwner());
            self::assertSame('2024-05-01 12:34:00', $position->getOpenedAt()?->format('Y-m-d H:i:s'));
            self::assertTrue($position->isIsDca());
            self::assertTrue($position->isIsOpened());
        }
    }

    public function testCoinWithoutPriceGetsNoQuantity(): void
    {
        $user = new User();
        $dca = new StrategyDca($user)->addPart(new CoinPercentDca()->setCoin(new Cryptocurrency()->setPriceUsd(0))->setPercent(100));

        $positions = new DcaPositionGenerator(new MockClock())->generate($dca, $user, 1000);

        self::assertSame(0.0, $positions[0]->getNbCoins());
        self::assertSame(1000.0, $positions[0]->getEntryCost());
    }
}
