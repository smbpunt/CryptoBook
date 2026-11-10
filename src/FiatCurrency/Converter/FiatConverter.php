<?php

namespace App\FiatCurrency\Converter;

use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Exception\MissingRateException;

/**
 * Converts amounts between USD and the other fiat currencies, with the Fixer rates.
 * Both ways use the USD rate of the currency: it is all a currency needs to be used.
 */
final readonly class FiatConverter
{
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
        return $amountUsd / $to->rateTo(FiatCurrency::KEY_USD);
    }
}
