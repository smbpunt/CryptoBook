<?php

namespace App\Tests\FiatCurrency\Converter;

use App\FiatCurrency\Converter\FiatConverter;
use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Exception\MissingRateException;
use PHPUnit\Framework\TestCase;

final class FiatConverterTest extends TestCase
{
    private FiatCurrency $usd;
    private FiatCurrency $eur;
    private FiatConverter $converter;

    protected function setUp(): void
    {
        $this->usd = new FiatCurrency()->setFixerKey('USD');
        $this->eur = new FiatCurrency()->setFixerKey('EUR')->setRates(['USD' => 1.25]);
        $this->converter = new FiatConverter();
    }

    public function testConvertsEuros(): void
    {
        self::assertSame(1.25, $this->converter->toUsd(1.0, $this->eur));
        self::assertEqualsWithDelta(125.0, $this->converter->toUsd(100, $this->eur), 1e-9);
        self::assertEqualsWithDelta(80.0, $this->converter->fromUsd(100, $this->eur), 1e-9);
    }

    public function testDollarsAreLeftAsIs(): void
    {
        self::assertSame(100.0, $this->converter->toUsd(100, $this->usd));
        self::assertSame(100.0, $this->converter->fromUsd(100, $this->usd));
    }

    public function testOnlyTheUsdRateOfTheCurrencyIsNeeded(): void
    {
        $this->expectException(MissingRateException::class);

        $this->converter->fromUsd(100, new FiatCurrency()->setFixerKey('CHF')->setRates(['EUR' => 1.05]));
    }
}
