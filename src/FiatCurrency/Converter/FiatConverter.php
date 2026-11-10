<?php

namespace App\FiatCurrency\Converter;

use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Repository\FiatCurrencyRepository;

/**
 * Converts amounts between USD and the other fiat currencies, with the Fixer rates.
 */
final readonly class FiatConverter
{
    public function __construct(private FiatCurrencyRepository $fiatCurrencyRepository)
    {
    }

    public function toUsd(float $amount, FiatCurrency $from): float
    {
        if (FiatCurrency::KEY_USD === $from->getFixerKey()) {
            return $amount;
        }

        return $amount * $from->getRates()[FiatCurrency::KEY_USD];
    }

    public function fromUsd(float $amountUsd, FiatCurrency $to): float
    {
        if (FiatCurrency::KEY_USD === $to->getFixerKey()) {
            return $amountUsd;
        }

        return $amountUsd * $this->fiatCurrencyRepository->getUsd()->getRates()[$to->getFixerKey()];
    }
}
