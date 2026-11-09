<?php

namespace App\Tests\Unit\Service;

use App\Entity\CoinPercentDca;
use App\Entity\Cryptocurrency;
use App\Entity\StrategyDca;
use App\Entity\User;
use App\Form\DcaAutoType;
use App\Repository\CryptocurrencyRepository;
use App\Service\DcaService;
use PHPUnit\Framework\TestCase;

final class DcaServiceTest extends TestCase
{
    public function testGeneratePositionsSplitsAmountByPercent(): void
    {
        $user = new User();
        $btc = (new Cryptocurrency())->setPriceUsd(50000);
        $eth = (new Cryptocurrency())->setPriceUsd(3000);
        $dca = (new StrategyDca($user))
            ->addPart((new CoinPercentDca())->setCoin($btc)->setPercent(60))
            ->addPart((new CoinPercentDca())->setCoin($eth)->setPercent(40));

        $positions = $this->service()->generatePositions($dca, $user, 1000);

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
            self::assertTrue($position->isIsDca());
            self::assertTrue($position->isIsOpened());
        }
    }

    public function testGenerateDcaAutoPositionsSpreadsTotalOverDates(): void
    {
        $coin = new Cryptocurrency();
        $repository = $this->createStub(CryptocurrencyRepository::class);
        $repository->method('find')->willReturn($coin);

        $positions = $this->service($repository)
            ->generateDcaAutoPositions(new User(), '1', DcaAutoType::$WEEKLY, '3', '300', '2024-01-01 10:00');

        self::assertCount(3, $positions);
        self::assertSame(
            ['2024-01-01 10:00', '2024-01-08 10:00', '2024-01-15 10:00'],
            array_map(static fn ($p) => $p->getOpenedAt()->format('Y-m-d H:i'), $positions),
        );
        foreach ($positions as $position) {
            self::assertSame($coin, $position->getCoin());
            self::assertSame(100.0, $position->getEntryCost());
            self::assertTrue($position->isIsDca());
        }
    }

    public function testGenerateDcaAutoPositionsReturnsNothingOnInvalidInput(): void
    {
        $repository = $this->createStub(CryptocurrencyRepository::class);
        $repository->method('find')->willReturn(new Cryptocurrency());
        $service = $this->service($repository);

        self::assertSame([], $service->generateDcaAutoPositions(new User(), '1', 'yearly', '3', '300', '2024-01-01'));
        self::assertSame([], $service->generateDcaAutoPositions(new User(), '1', DcaAutoType::$DAILY, '3', '300', 'not a date'));
    }

    public function testGenerateDcaAutoPositionsReturnsNothingForUnknownCoin(): void
    {
        self::assertSame([], $this->service()->generateDcaAutoPositions(new User(), '42', DcaAutoType::$DAILY, '3', '300', '2024-01-01'));
    }

    private function service(?CryptocurrencyRepository $repository = null): DcaService
    {
        return new DcaService($repository ?? $this->createStub(CryptocurrencyRepository::class));
    }
}
