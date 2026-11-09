<?php

namespace App\Wallet\Dto;

/**
 * Value of a user's wallet, in USD.
 */
final readonly class WalletValue
{
    public float $cryptoUsd;
    public float $stableUsd;

    /**
     * @param list<Holding> $cryptoHoldings sorted by value, highest first
     * @param list<Holding> $stableHoldings
     */
    public function __construct(
        public array $cryptoHoldings,
        public array $stableHoldings,
    ) {
        $this->cryptoUsd = self::sum($cryptoHoldings);
        $this->stableUsd = self::sum($stableHoldings);
    }

    public function totalUsd(): float
    {
        return $this->cryptoUsd + $this->stableUsd;
    }

    /**
     * @param list<Holding> $holdings
     */
    private static function sum(array $holdings): float
    {
        return array_sum(array_map(static fn (Holding $holding): float => $holding->valueUsd, $holdings));
    }
}
