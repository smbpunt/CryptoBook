<?php

namespace App\Wallet\Dto;

/**
 * Quantity of one coin held by a user, across all their open positions.
 */
final readonly class Holding
{
    public function __construct(
        public string $symbol,
        public string $libelle,
        public ?string $color,
        public ?string $urlImgThumb,
        public float $quantity,
        public float $priceUsd,
        public float $valueUsd,
        /** Share of this holding in its group (crypto or stable), in percent. */
        public float $percent,
    ) {
    }
}
