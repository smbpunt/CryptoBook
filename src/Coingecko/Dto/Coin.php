<?php

namespace App\Coingecko\Dto;

/**
 * A coin as described by CoinGecko. Fields missing from the answer are null.
 */
final readonly class Coin
{
    public function __construct(
        public ?string $name = null,
        public ?string $symbol = null,
        public ?float $priceUsd = null,
        public ?float $marketCapUsd = null,
        public ?string $imageThumb = null,
        public ?string $imageSmall = null,
        public ?string $imageLarge = null,
    ) {
    }
}
