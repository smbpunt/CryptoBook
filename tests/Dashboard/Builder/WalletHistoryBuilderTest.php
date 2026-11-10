<?php

namespace App\Tests\Dashboard\Builder;

use App\Dashboard\Builder\WalletHistoryBuilder;
use App\Dashboard\Dto\WalletHistoryPoint;
use App\Tests\Fixtures\Dataset;
use App\Wallet\Entity\WalletSnapshot;
use App\Wallet\Enum\HistoryPeriod;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

final class WalletHistoryBuilderTest extends KernelTestCase
{
    use ClockSensitiveTrait;

    private EntityManagerInterface $em;
    private Dataset $data;
    private WalletHistoryBuilder $builder;

    protected function setUp(): void
    {
        self::mockTime('2026-11-10 14:20 UTC');
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->data = Dataset::load($this->em);
        $this->builder = self::getContainer()->get(WalletHistoryBuilder::class);
    }

    public function testEndsOnTheCurrentTotal(): void
    {
        $this->snapshot('2026-11-09 15:00', 20000);
        $this->snapshot('2026-11-10 14:00', 24000);

        $history = $this->builder->build($this->data->user, HistoryPeriod::Day, 25000);

        // The deposit of 1 000 € (2024-01-01) at the migration rate.
        $invested = 1073.941;
        self::assertEqualsWithDelta([
            new WalletHistoryPoint(new \DateTimeImmutable('2026-11-09 15:00 UTC'), 20000, $invested),
            new WalletHistoryPoint(new \DateTimeImmutable('2026-11-10 14:00 UTC'), 24000, $invested),
            new WalletHistoryPoint(new \DateTimeImmutable('2026-11-10 14:20 UTC'), 25000, $invested),
        ], $history->points, 1e-6);
        self::assertSame(5000.0, $history->changeUsd);
        self::assertSame(25.0, $history->changePercent);
        self::assertTrue($history->coversPeriod);
        self::assertTrue($history->hasDeposits());
    }

    public function testHistoryShorterThanThePeriod(): void
    {
        $this->snapshot('2026-11-01 10:00', 20000);

        $history = $this->builder->build($this->data->user, HistoryPeriod::Month, 25000);

        self::assertFalse($history->coversPeriod);
        self::assertEquals(new \DateTimeImmutable('2026-11-01 10:00 UTC'), $history->startedAt());
    }

    /**
     * Daily periods start on the last snapshot of their first day: up to one day after the start of the period
     * (2026-10-11 14:20 for 30 days), the history still covers it.
     *
     * @return iterable<string, array{string, bool}>
     */
    public static function dailyStarts(): iterable
    {
        yield 'within the first day' => ['2026-10-12 14:00', true];
        yield 'after the first day' => ['2026-10-12 15:00', false];
    }

    #[DataProvider('dailyStarts')]
    public function testDailyPeriodCoversItsFirstDay(string $firstSnapshot, bool $covers): void
    {
        $this->snapshot($firstSnapshot, 20000);

        self::assertSame($covers, $this->builder->build($this->data->user, HistoryPeriod::Month, 25000)->coversPeriod);
    }

    private function snapshot(string $at, float $valueUsd): void
    {
        $this->em->persist(new WalletSnapshot($this->data->user, new \DateTimeImmutable($at.' UTC'), $valueUsd));
        $this->em->flush();
    }
}
