<?php

namespace App\FiatCurrency\Exception;

/**
 * The rates of a currency could not be fetched: Fixer failed, or gave no USD rate.
 */
final class RatesUnavailableException extends \RuntimeException
{
    public static function for(string $fixerKey, string $reason, ?\Throwable $previous = null): self
    {
        return new self(\sprintf('No rates for "%s": %s', $fixerKey, $reason), previous: $previous);
    }
}
