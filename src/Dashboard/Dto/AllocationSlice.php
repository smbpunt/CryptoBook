<?php

namespace App\Dashboard\Dto;

/**
 * One slice of the allocation donut.
 */
final readonly class AllocationSlice
{
    public const float OTHERS_BELOW_PERCENT = 0.3;
    private const array FALLBACK_COLORS = ['var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)'];

    public function __construct(
        public string $label,
        public string $color,
        /** Share of the crypto wallet, in %. */
        public float $percent,
        /** Sum of the shares of the slices before this one, in %. */
        public float $offset,
    ) {
    }

    /**
     * Holdings above 0.3 % get a slice each, the others are grouped in "Autres". A coin without a color gets a chart token.
     *
     * @param list<DashboardHolding> $holdings sorted by value, highest first
     *
     * @return list<self>
     */
    public static function fromHoldings(array $holdings): array
    {
        $slices = [];
        $offset = 0.0;
        $othersPercent = 0.0;
        $fallback = 0;
        foreach ($holdings as $holding) {
            if ($holding->percent <= self::OTHERS_BELOW_PERCENT) {
                $othersPercent += $holding->percent;
                continue;
            }

            $color = $holding->color ?: self::FALLBACK_COLORS[$fallback++ % \count(self::FALLBACK_COLORS)];
            $slices[] = new self(strtoupper($holding->symbol), $color, $holding->percent, $offset);
            $offset += $holding->percent;
        }

        if ($othersPercent > 0) {
            $slices[] = new self('Autres', 'var(--muted-foreground)', $othersPercent, $offset);
        }

        return $slices;
    }
}
