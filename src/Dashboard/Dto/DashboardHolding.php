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
        public string $color,
        public ?string $thumbUrl,
        public float $quantity,
        public float $priceUsd,
        public float $valueUsd,
        /** Share of the holding in its group (crypto or stable), in %. */
        public float $percent,
    ) {
    }

    /**
     * The color goes into style attributes: anything but a hex color is dropped (a color derived from the symbol replaces it).
     */
    private static function hexOrNull(?string $color): ?string
    {
        return null !== $color && preg_match('/^#[0-9a-f]{3,8}$/i', $color) ? $color : null;
    }

    /**
     * The same color for a symbol on every page load, whatever its case.
     */
    public static function colorFromSymbol(string $symbol): string
    {
        return \sprintf('hsl(%d 65%% 55%%)', crc32(strtolower($symbol)) % 360);
    }

    public static function fromWalletHolding(WalletHolding $holding): self
    {
        $symbol = (string) $holding->coin->getSymbol();

        return new self(
            (string) $holding->coin->getLibelle(),
            $symbol,
            self::hexOrNull($holding->coin->getColor()) ?? self::colorFromSymbol($symbol),
            $holding->coin->getUrlImgThumb(),
            $holding->quantity,
            $holding->priceUsd,
            $holding->valueUsd,
            $holding->percent,
        );
    }
}
