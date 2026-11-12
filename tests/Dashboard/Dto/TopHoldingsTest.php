<?php

namespace App\Tests\Dashboard\Dto;

use App\Dashboard\Dto\DashboardHolding;
use App\Dashboard\Dto\TopHoldings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TopHoldingsTest extends TestCase
{
    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function rowsProvider(): iterable
    {
        yield 'fixtures' => [7, 1, 7];
        yield 'single slice' => [1, 0, 2];
        yield 'long legend' => [10, 2, 8];
        yield 'many stablecoins' => [5, 3, 7];
    }

    #[DataProvider('rowsProvider')]
    public function testRowsFollowTheHeightOfTheAllocationCard(int $slices, int $stables, int $rows): void
    {
        self::assertSame($rows, TopHoldings::rowsFor($slices, $stables));
    }

    public function testGroupsTheSmallestHoldingsInOthers(): void
    {
        $holdings = $this->holdings([500, 200, 100, 80, 60, 50, 3, 2, 1]);

        $top = TopHoldings::fromHoldings($holdings, 7, 1000);

        self::assertSame(\array_slice($holdings, 0, 6), $top->holdings);
        self::assertSame(3, $top->othersCount);
        self::assertSame(6.0, $top->othersValueUsd);
        self::assertEqualsWithDelta(0.6, $top->othersPercent, 1e-9);
    }

    public function testShowsEverythingWithinTheLimit(): void
    {
        $holdings = $this->holdings([7, 6, 5, 4, 3, 2, 1]);

        $top = TopHoldings::fromHoldings($holdings, 7, 28);

        self::assertSame($holdings, $top->holdings);
        self::assertSame(0, $top->othersCount);
    }

    public function testOthersGroupsAtLeastTwoHoldings(): void
    {
        $top = TopHoldings::fromHoldings($this->holdings([8, 7, 6, 5, 4, 3, 2, 1]), 7, 36);

        self::assertCount(6, $top->holdings);
        self::assertSame(2, $top->othersCount);
    }

    public function testNoPercentWithoutTotal(): void
    {
        $top = TopHoldings::fromHoldings($this->holdings([0, 0, 0]), 2, 0);

        self::assertSame(2, $top->othersCount);
        self::assertSame(0.0, $top->othersPercent);
    }

    /**
     * @param list<float|int> $values
     *
     * @return list<DashboardHolding>
     */
    private function holdings(array $values): array
    {
        return array_map(static fn (float|int $value): DashboardHolding => new DashboardHolding("c$value", "c$value", '#000000', null, 1, $value, $value, 0), $values);
    }
}
