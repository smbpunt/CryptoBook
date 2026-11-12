<?php

namespace App\Deposit\Dto;

/**
 * Total deposited, in USD, from a date on. A null date is the beginning: deposits without a date.
 */
final readonly class DepositStep
{
    public function __construct(
        public ?\DateTimeImmutable $from,
        public float $totalUsd,
    ) {
    }
}
