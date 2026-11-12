<?php

namespace App\Wallet\Dto;

/**
 * Figures read from the whole snapshot history of a user. Every field is null without snapshots.
 */
final readonly class WalletSnapshotStats
{
    public function __construct(
        public ?float $highUsd = null,
        public ?\DateTimeImmutable $highAt = null,
        /** Last snapshot between 48 and 24 hours ago. */
        public ?float $dayAgoUsd = null,
        public ?\DateTimeImmutable $lastRecordedAt = null,
    ) {
    }
}
