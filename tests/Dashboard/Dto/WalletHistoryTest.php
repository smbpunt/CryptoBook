<?php

namespace App\Tests\Dashboard\Dto;

use App\Dashboard\Dto\WalletHistory;
use App\Dashboard\Dto\WalletHistoryPoint;
use App\Wallet\Enum\HistoryPeriod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WalletHistoryTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function spans(): iterable
    {
        yield '24 hours' => ['+24 hours', 'hour'];
        yield '24 hours and 1 second' => ['+24 hours +1 second', 'day'];
        yield '124 days' => ['+124 days', 'day'];
        yield '124 days and 1 second' => ['+124 days +1 second', 'month'];
    }

    #[DataProvider('spans')]
    public function testTickUnitFollowsTheSpanDisplayed(string $span, string $unit): void
    {
        $start = new \DateTimeImmutable('2026-07-01 12:00 UTC');
        $history = new WalletHistory(HistoryPeriod::All, [
            new WalletHistoryPoint($start, 1000, 0),
            new WalletHistoryPoint($start->modify('+1 hour'), 1000, 0),
            new WalletHistoryPoint($start->modify($span), 1000, 0),
        ]);

        self::assertSame($unit, $history->tickUnit());
    }
}
