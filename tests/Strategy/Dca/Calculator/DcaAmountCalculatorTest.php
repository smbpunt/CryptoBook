<?php

namespace App\Tests\Strategy\Dca\Calculator;

use App\Strategy\Dca\Calculator\DcaAmountCalculator;
use App\Strategy\Dca\Entity\StrategyDca;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DcaAmountCalculatorTest extends KernelTestCase
{
    public function testEurosAtTheFixerRatePlusTheFarming(): void
    {
        $dca = new StrategyDca(new User())->setFiatToDcaEur(100)->setFarmingToDcaUsd(20);

        // Migration rates: 1 EUR = 1.073941 USD.
        self::assertEqualsWithDelta(127.3941, self::getContainer()->get(DcaAmountCalculator::class)->amountUsd($dca), 1e-9);
    }
}
