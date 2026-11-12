<?php

namespace App\Dashboard\Dto;

/**
 * One point of the wallet chart: value of the wallet and total deposited at that time, in USD.
 */
final readonly class WalletHistoryPoint
{
    public function __construct(
        public \DateTimeImmutable $at,
        public float $valueUsd,
        public float $investedUsd,
    ) {
    }
}
