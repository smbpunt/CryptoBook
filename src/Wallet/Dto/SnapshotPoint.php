<?php

namespace App\Wallet\Dto;

/**
 * Value of a wallet at a given time, in USD.
 */
final readonly class SnapshotPoint
{
    public function __construct(
        public \DateTimeImmutable $recordedAt,
        public float $valueUsd,
    ) {
    }
}
