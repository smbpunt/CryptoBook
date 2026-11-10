<?php

namespace App\Tests\Strategy\Dca\Calculator;

use App\FiatCurrency\Converter\FiatConverter;
use App\FiatCurrency\Repository\FiatCurrencyRepository;
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

    public function testNoEuroRateNeededWithoutFiatAmount(): void
    {
        $repository = $this->createStub(FiatCurrencyRepository::class);
        $repository->method('getByFixerKey')->willThrowException(new \LogicException('The EUR currency is missing.'));
        $calculator = new DcaAmountCalculator(new FiatConverter($repository), $repository);

        self::assertSame(20.0, $calculator->amountUsd(new StrategyDca(new User())->setFiatToDcaEur(0)->setFarmingToDcaUsd(20)));
    }

    public function testNullWithoutEuroRate(): void
    {
        self::getContainer()->get(FiatCurrencyRepository::class)->getByFixerKey('EUR')->setRates([]);
        $dca = new StrategyDca(new User())->setFiatToDcaEur(100)->setFarmingToDcaUsd(20);

        self::assertNull(self::getContainer()->get(DcaAmountCalculator::class)->amountUsd($dca));
    }
}
