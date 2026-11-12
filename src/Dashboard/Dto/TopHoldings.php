<?php

namespace App\Dashboard\Dto;

/**
 * The rows of the "Wallet crypto" card: the biggest holdings, the rest grouped in "Autres",
 * so the card ends at the bottom of the "Répartition" card.
 */
final readonly class TopHoldings
{
    // Heights in px, measured in Chrome at 1440 px: re-measure if the layout of
    // templates/dashboard/_allocation.html.twig or templates/dashboard/_holdings.html.twig changes.
    /** "Répartition" without legend line or stablecoin. */
    private const float ALLOCATION_BASE = 276.0;
    /** One line of the legend or of the stablecoins. */
    private const float ALLOCATION_LINE = 26.57;
    /** Title and separator of the stablecoins block. */
    private const float ALLOCATION_STABLE_BASE = 59.5;
    /** "Wallet crypto" without row. */
    private const float HOLDINGS_BASE = 134.0;
    /** One row of the table. */
    private const float HOLDINGS_ROW = 59.0;
    /** Accepted overflow. */
    private const float TOLERANCE = 4.0;

    /**
     * @param list<DashboardHolding> $holdings the rows shown, highest value first
     */
    public function __construct(
        public array $holdings,
        /** Number of holdings grouped in "Autres": 0, or 2 and more. */
        public int $othersCount,
        public float $othersValueUsd,
        /** Share of the grouped holdings in the crypto wallet, in %. */
        public float $othersPercent,
    ) {
    }

    /**
     * Number of rows of the table, "Autres" included, for an allocation card of this many legend lines and stablecoins.
     */
    public static function rowsFor(int $slices, int $stables): int
    {
        $allocation = self::ALLOCATION_BASE + self::ALLOCATION_LINE * $slices
            + ($stables > 0 ? self::ALLOCATION_STABLE_BASE + self::ALLOCATION_LINE * $stables : 0.0);

        return (int) floor(($allocation - self::HOLDINGS_BASE + self::TOLERANCE) / self::HOLDINGS_ROW);
    }

    /**
     * A single holding past the limit is shown itself: "Autres" always groups 2 holdings or more.
     *
     * @param list<DashboardHolding> $holdings sorted by value, highest first
     * @param float                  $totalUsd value of the crypto wallet
     */
    public static function fromHoldings(array $holdings, int $rows, float $totalUsd): self
    {
        if (\count($holdings) <= $rows) {
            return new self($holdings, 0, 0.0, 0.0);
        }

        $others = \array_slice($holdings, $rows - 1);
        $othersValueUsd = array_sum(array_map(static fn (DashboardHolding $holding): float => $holding->valueUsd, $others));

        return new self(
            \array_slice($holdings, 0, $rows - 1),
            \count($others),
            $othersValueUsd,
            $totalUsd > 0 ? $othersValueUsd * 100 / $totalUsd : 0.0,
        );
    }
}
