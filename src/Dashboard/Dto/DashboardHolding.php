<?php

namespace App\Dashboard\Dto;

use App\Wallet\Dto\WalletHolding;

/**
 * One coin of the dashboard wallet, without the entity so it can be cached.
 */
final readonly class DashboardHolding
{
    public function __construct(
        public string $name,
        public string $symbol,
        public ?string $color,
        public ?string $thumbUrl,
        public float $quantity,
        public float $valueUsd,
        /** Share of the holding in its group (crypto or stable), in %. */
        public float $percent,
    ) {
    }

    public static function fromWalletHolding(WalletHolding $holding): self
    {
        return new self(
            (string) $holding->coin->getLibelle(),
            (string) $holding->coin->getSymbol(),
            $holding->coin->getColor(),
            $holding->coin->getUrlImgThumb(),
            $holding->quantity,
            $holding->valueUsd,
            $holding->percent,
        );
    }
}
