<?php

namespace App\FiatCurrency\Converter;

use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Exception\MissingRateException;
use App\FiatCurrency\Repository\FiatCurrencyRepository;

/**
 * Converts amounts between USD and the other fiat currencies, with the Fixer rates.
 */
final readonly class FiatConverter
{
    public function __construct(private FiatCurrencyRepository $fiatCurrencyRepository)
    {
    }

    /**
     * @throws MissingRateException
     */
    public function toUsd(float $amount, FiatCurrency $from): float
    {
        return $amount * $from->rateTo(FiatCurrency::KEY_USD);
    }

    /**
     * @throws MissingRateException
     */
    public function fromUsd(float $amountUsd, FiatCurrency $to): float
    {
        if (FiatCurrency::KEY_USD === $to->getFixerKey()) {
            return $amountUsd;
        }

        return $amountUsd * $this->fiatCurrencyRepository->getUsd()->rateTo((string) $to->getFixerKey());
    }
}
