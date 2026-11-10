<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Repository\FiatCurrencyRepository;
use App\Fixer\Client\FixerClient;
use App\Service\FiatExchangeRatesService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;

final class FiatExchangeRatesServiceTest extends TestCase
{
    private FiatCurrency $usd;
    private FiatCurrency $eur;
    private FiatExchangeRatesService $service;

    protected function setUp(): void
    {
        $this->usd = new FiatCurrency()->setFixerKey('USD')->setRates(['EUR' => 0.9]);
        $this->eur = new FiatCurrency()->setFixerKey('EUR')->setRates(['USD' => 1.1]);

        $repository = $this->createStub(FiatCurrencyRepository::class);
        $repository->method('findOneBy')->willReturnCallback(
            fn (array $criteria) => ['USD' => $this->usd, 'EUR' => $this->eur][$criteria['fixerKey']],
        );
        $this->service = new FiatExchangeRatesService(new FixerClient(new MockHttpClient()), $repository);
    }

    public function testConversionsForEuroUser(): void
    {
        $user = new User()->setFavoriteFiatCurrency($this->eur);

        self::assertSame(1.1, $this->service->getUsdRateFromFavoriteCurrency($user));
        self::assertEqualsWithDelta(110.0, $this->service->toUsd(100, $user), 1e-9);
        self::assertEqualsWithDelta(90.0, $this->service->toFavoriteCurrency(100, $user), 1e-9);
    }

    public function testConversionsForDollarUser(): void
    {
        $user = new User()->setFavoriteFiatCurrency($this->usd);

        self::assertSame(1.0, $this->service->getUsdRateFromFavoriteCurrency($user));
        self::assertSame(100.0, $this->service->toFavoriteCurrency(100, $user));
        self::assertSame(100.0, $this->service->toUsd(100, $user));
    }

    public function testFallsBackToDollarWithoutFavoriteCurrency(): void
    {
        $user = new User();

        self::assertSame(100.0, $this->service->toFavoriteCurrency(100, $user));
        self::assertSame(100.0, $this->service->toUsd(100, $user));
    }
}
