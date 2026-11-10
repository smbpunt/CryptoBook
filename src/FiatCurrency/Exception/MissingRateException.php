<?php

namespace App\FiatCurrency\Exception;

/**
 * The currency has no rate to the other one: it was never updated, or Fixer does not know the symbol.
 */
final class MissingRateException extends \RuntimeException
{
    public static function between(string $from, string $to): self
    {
        return new self(\sprintf('No %s to %s rate: run fiat:updateRates, and check that Fixer knows both symbols.', $from, $to));
    }
}
