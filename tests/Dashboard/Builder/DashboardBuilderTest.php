<?php

namespace App\Tests\Dashboard\Builder;

use App\Dashboard\Builder\DashboardBuilder;
use App\Dashboard\Dto\DashboardHolding;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Wallet values are covered by WalletValueCalculatorTest: only what the builder adds is checked here.
 * Migration rates: 1 EUR = 1.073941 USD, 1 USD = 0.930795 EUR.
 */
final class DashboardBuilderTest extends KernelTestCase
{
    private Dataset $data;
    private DashboardBuilder $builder;

    protected function setUp(): void
    {
        $this->data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $this->builder = self::getContainer()->get(DashboardBuilder::class);
    }

    public function testBuildsTheUserDashboard(): void
    {
        $dashboard = $this->builder->build($this->data->user);

        self::assertEquals(new DashboardHolding('Bitcoin', 'btc', null, null, 0.5, 25000.0, 100.0), $dashboard->cryptoHoldings[0]);
        self::assertEquals(new DashboardHolding('USD Coin', 'usdc', null, null, 1000.0, 1000.0, 100.0), $dashboard->stableHoldings[0]);
        // 1 000 € deposited, 500 USDC borrowed, 2 ETH at 5 % + LP of 4 000 $ at 10 %.
        self::assertEqualsWithDelta(1073.941, $dashboard->depositUsd, 1e-9);
        self::assertSame(500.0, $dashboard->loanUsd);
        self::assertEqualsWithDelta(600.0, $dashboard->yearFarmingUsd, 1e-9);
        self::assertSame('€', $dashboard->currencySymbol);
        self::assertSame(1.073941, $dashboard->usdPerFavorite);
        self::assertSame(0.930795, $dashboard->favoritePerUsd);
        self::assertEqualsWithDelta(93.0795, $dashboard->toFavorite(100), 1e-9);
    }

    public function testOnlyCountsTheUserData(): void
    {
        // The deposit, loan and farmings of the Dataset belong to the other user.
        $dashboard = $this->builder->build($this->data->admin);

        self::assertSame(0.0, $dashboard->depositUsd);
        self::assertSame(0.0, $dashboard->loanUsd);
        self::assertSame(0.0, $dashboard->yearFarmingUsd);
        self::assertSame('$', $dashboard->currencySymbol);
        self::assertSame(1.0, $dashboard->usdPerFavorite);
    }
}
