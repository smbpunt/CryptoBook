<?php

namespace App\Tests\User\Resolver;

use App\Entity\User;
use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Repository\FiatCurrencyRepository;
use App\User\Resolver\FavoriteCurrencyResolver;
use PHPUnit\Framework\TestCase;

final class FavoriteCurrencyResolverTest extends TestCase
{
    public function testFavoriteCurrencyOfTheUser(): void
    {
        $eur = new FiatCurrency()->setFixerKey('EUR');

        self::assertSame($eur, $this->resolver(new FiatCurrency())->resolve(new User()->setFavoriteFiatCurrency($eur)));
    }

    public function testFallsBackToDollar(): void
    {
        $usd = new FiatCurrency()->setFixerKey('USD');

        self::assertSame($usd, $this->resolver($usd)->resolve(new User()));
    }

    private function resolver(FiatCurrency $usd): FavoriteCurrencyResolver
    {
        $repository = $this->createStub(FiatCurrencyRepository::class);
        $repository->method('getUsd')->willReturn($usd);

        return new FavoriteCurrencyResolver($repository);
    }
}
