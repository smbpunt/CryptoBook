<?php

namespace App\Wallet\Dto;

/**
 * Value of a wallet in USD: crypto and stable holdings, sorted by value.
 */
final readonly class WalletValuation
{
    public float $totalUsd;

    /**
     * @param list<WalletHolding> $cryptoHoldings
     * @param list<WalletHolding> $stableHoldings
     */
    public function __construct(
        public array $cryptoHoldings,
        public array $stableHoldings,
        public float $cryptoUsd,
        public float $stableUsd,
    ) {
        $this->totalUsd = $cryptoUsd + $stableUsd;
    }
}
