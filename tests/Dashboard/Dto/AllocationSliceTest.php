<?php

namespace App\Tests\Dashboard\Dto;

use App\Dashboard\Dto\AllocationSlice;
use App\Dashboard\Dto\DashboardHolding;
use PHPUnit\Framework\TestCase;

final class AllocationSliceTest extends TestCase
{
    public function testGroupsSmallHoldingsInOthers(): void
    {
        $slices = AllocationSlice::fromHoldings([
            $this->holding('btc', '#F7931A', 80),
            $this->holding('eth', null, 19.5),
            $this->holding('doge', '#C2A633', 0.3),
            $this->holding('pepe', null, 0.2),
        ]);

        self::assertEquals([
            new AllocationSlice('BTC', '#F7931A', 80, 0),
            new AllocationSlice('ETH', 'var(--chart-3)', 19.5, 80),
            new AllocationSlice('Autres', 'var(--muted-foreground)', 0.5, 99.5),
        ], $slices);
    }

    public function testNoOthersWhenEveryHoldingIsVisible(): void
    {
        self::assertCount(1, AllocationSlice::fromHoldings([$this->holding('btc', null, 100)]));
        self::assertSame([], AllocationSlice::fromHoldings([]));
    }

    private function holding(string $symbol, ?string $color, float $percent): DashboardHolding
    {
        return new DashboardHolding($symbol, $symbol, $color, null, 1, 1, 1, $percent);
    }
}
