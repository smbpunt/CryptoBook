<?php

namespace App\Tests\FiatCurrency\Converter;

use App\FiatCurrency\Converter\FiatConverter;
use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Repository\FiatCurrencyRepository;
use PHPUnit\Framework\TestCase;

final class FiatConverterTest extends TestCase
{
    private FiatCurrency $usd;
    private FiatCurrency $eur;
    private FiatConverter $converter;

    protected function setUp(): void
    {
        $this->usd = new FiatCurrency()->setFixerKey('USD')->setRates(['EUR' => 0.9]);
        $this->eur = new FiatCurrency()->setFixerKey('EUR')->setRates(['USD' => 1.1]);

        $repository = $this->createStub(FiatCurrencyRepository::class);
        $repository->method('getUsd')->willReturn($this->usd);
        $this->converter = new FiatConverter($repository);
    }

    public function testConvertsEuros(): void
    {
        self::assertSame(1.1, $this->converter->toUsd(1.0, $this->eur));
        self::assertEqualsWithDelta(110.0, $this->converter->toUsd(100, $this->eur), 1e-9);
        self::assertEqualsWithDelta(90.0, $this->converter->fromUsd(100, $this->eur), 1e-9);
    }

    public function testDollarsAreLeftAsIs(): void
    {
        self::assertSame(100.0, $this->converter->toUsd(100, $this->usd));
        self::assertSame(100.0, $this->converter->fromUsd(100, $this->usd));
    }
}
