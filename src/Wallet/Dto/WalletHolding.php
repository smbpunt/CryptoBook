<?php

namespace App\Wallet\Dto;

use App\Entity\Cryptocurrency;

/**
 * One coin of a wallet, valued at its current price.
 */
final readonly class WalletHolding
{
    public function __construct(
        public Cryptocurrency $coin,
        public float $quantity,
        public float $priceUsd,
        public float $valueUsd,
        /** Share of the holding in its group (crypto or stable), in %. */
        public float $percent,
    ) {
    }
}
