<?php

namespace App\Position\Dto;

use App\Cryptocurrency\Entity\Cryptocurrency;

/**
 * Quantity of a coin held by an owner.
 */
final readonly class CoinQuantity
{
    public function __construct(
        public Cryptocurrency $coin,
        public int $ownerId,
        public float $quantity,
    ) {
    }
}
