<?php

namespace App\Tests\Unit\Service;

use App\Entity\FiatCurrency;
use App\Entity\User;
use App\Fixer\Client\FixerClient;
use App\Repository\FiatCurrencyRepository;
use App\Service\FiatExchangeRatesService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\MockHttpClient;

final class FiatExchangeRatesServiceTest extends TestCase
{
    private FiatCurrency $usd;
    private FiatCurrency $eur;

    protected function setUp(): void
    {
        $this->usd = new FiatCurrency()->setFixerKey('USD')->setRates(['EUR' => 0.9]);
        $this->eur = new FiatCurrency()->setFixerKey('EUR')->setRates(['USD' => 1.1]);
    }

    public function testConversionsForEuroUser(): void
    {
        $service = $this->service($this->eur);

        self::assertSame(1.1, $service->getUsdRateFromFavoriteCurrency());
        self::assertEqualsWithDelta(110.0, $service->toUsd(100), 1e-9);
        self::assertEqualsWithDelta(90.0, $service->toFavoriteCurrency(100), 1e-9);
    }

    public function testConversionsForDollarUser(): void
    {
        $service = $this->service($this->usd);

        self::assertSame(1.0, $service->getUsdRateFromFavoriteCurrency());
        self::assertSame(100.0, $service->toFavoriteCurrency(100));
        self::assertSame(100.0, $service->toUsd(100));
        self::assertSame(100.0, $service->toUsd(100, $this->usd));
    }

    public function testFallsBackToDollarWithoutLoggedInUser(): void
    {
        $repository = $this->createStub(FiatCurrencyRepository::class);
        $repository->method('findOneBy')->willReturn($this->usd);

        $service = new FiatExchangeRatesService(new FixerClient(new MockHttpClient()), $repository, $this->createStub(Security::class));

        self::assertSame(100.0, $service->toFavoriteCurrency(100));
        self::assertSame(100.0, $service->toUsd(100));
    }

    private function service(FiatCurrency $favorite): FiatExchangeRatesService
    {
        $repository = $this->createStub(FiatCurrencyRepository::class);
        $repository->method('findOneBy')->willReturnCallback(
            fn (array $criteria) => ['USD' => $this->usd, 'EUR' => $this->eur][$criteria['fixerKey']],
        );
        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn(new User()->setFavoriteFiatCurrency($favorite));

        return new FiatExchangeRatesService(new FixerClient(new MockHttpClient()), $repository, $security);
    }
}
