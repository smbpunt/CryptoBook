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
            $this->holding('eth', '#627EEA', 19.5),
            $this->holding('doge', '#C2A633', 0.3),
            $this->holding('pepe', '#3D8130', 0.2),
        ]);

        self::assertEquals([
            new AllocationSlice('BTC', '#F7931A', 80, 0),
            new AllocationSlice('ETH', '#627EEA', 19.5, 80),
            new AllocationSlice('Autres', 'var(--muted-foreground)', 0.5, 99.5),
        ], $slices);
    }

    private function holding(string $symbol, string $color, float $percent): DashboardHolding
    {
        return new DashboardHolding($symbol, $symbol, $color, null, 1, 1, 1, $percent);
    }
}
