<?php

namespace App\Tests\Dashboard\Builder;

use App\Dashboard\Builder\DashboardBuilder;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The amounts of a filled dashboard are checked through the page by DashboardControllerTest.
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
