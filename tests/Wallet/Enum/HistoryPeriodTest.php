<?php

namespace App\Tests\Wallet\Enum;

use App\Wallet\Enum\HistoryPeriod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HistoryPeriodTest extends TestCase
{
    /**
     * @return iterable<string, array{HistoryPeriod, ?string, bool}>
     */
    public static function periods(): iterable
    {
        yield '24h' => [HistoryPeriod::Day, '2026-11-09 14:00', true];
        yield '7d' => [HistoryPeriod::Week, '2026-11-03 14:00', true];
        yield '30d' => [HistoryPeriod::Month, '2026-10-11 14:00', false];
        yield '90d' => [HistoryPeriod::Quarter, '2026-08-12 14:00', false];
        yield '1y' => [HistoryPeriod::Year, '2025-11-10 14:00', false];
        yield 'all' => [HistoryPeriod::All, null, false];
    }

    #[DataProvider('periods')]
    public function testStartAndGranularity(HistoryPeriod $period, ?string $since, bool $hourly): void
    {
        $now = new \DateTimeImmutable('2026-11-10 14:00 UTC');

        self::assertEquals(null === $since ? null : new \DateTimeImmutable($since.' UTC'), $period->since($now));
        self::assertSame($hourly, $period->isHourly());
    }
}
