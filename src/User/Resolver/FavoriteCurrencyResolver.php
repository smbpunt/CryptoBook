<?php

namespace App\User\Resolver;

use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Repository\FiatCurrencyRepository;
use App\User\Entity\User;

final readonly class FavoriteCurrencyResolver
{
    public function __construct(private FiatCurrencyRepository $fiatCurrencyRepository)
    {
    }

    /**
     * Favorite currency of the user, USD when none is set.
     */
    public function resolve(User $user): FiatCurrency
    {
        return $user->getFavoriteFiatCurrency() ?? $this->fiatCurrencyRepository->getUsd();
    }
}
