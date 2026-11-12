<?php

namespace App\Wallet\Dto;

/**
 * Outcome of a wallet history backfill.
 */
final readonly class BackfillReport
{
    public function __construct(
        public int $users,
        public int $snapshots,
        /** CoinGecko calls made, unknown coins included. */
        public int $fetchedCoins,
    ) {
    }
}
