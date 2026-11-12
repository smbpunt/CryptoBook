<?php

namespace App\Tests\Wallet\ValueObject;

use App\Wallet\ValueObject\HourlyPriceSeries;
use PHPUnit\Framework\TestCase;

final class HourlyPriceSeriesTest extends TestCase
{
    public function testPriceAtAnHour(): void
    {
        // Given out of order: the series sorts them.
        $series = HourlyPriceSeries::fromPoints([
            self::timestamp('15:40') => 130.0,
            self::timestamp('12:00') => 100.0,
            self::timestamp('13:10:40') => 110.0,
        ]);

        self::assertSame(100.0, $series->at(self::hour('12:00')));
        self::assertSame(110.0, $series->at(self::hour('13:00')));
        // Missing hour: last known price before it.
        self::assertSame(110.0, $series->at(self::hour('14:00')));
        // 15:40 is rounded to the nearest hour.
        self::assertSame(130.0, $series->at(self::hour('16:00')));
        self::assertSame(110.0, $series->at(self::hour('15:00')));
        // Before the first point: first price.
        self::assertSame(100.0, $series->at(self::hour('10:00')));
    }

    public function testEmptySeriesHasNoPrice(): void
    {
        self::assertNull(HourlyPriceSeries::fromPoints([])->at(self::hour('12:00')));
    }

    private static function hour(string $time): \DateTimeImmutable
    {
        return new \DateTimeImmutable('2026-11-10 '.$time.' UTC');
    }

    private static function timestamp(string $time): int
    {
        return self::hour($time)->getTimestamp();
    }
}
