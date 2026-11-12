<?php

namespace App\Shared\Twig;

use Twig\Attribute\AsTwigFilter;

/**
 * French formats of numbers, amounts and percentages: "12 345 €", "+1 234 €", "−2,5 %". The application locale is en.
 */
final class AmountExtension
{
    private const string NBSP = "\u{00A0}";
    private const string MINUS = "\u{2212}";

    /** @var array<int, \NumberFormatter> by number of decimals */
    private array $formatters = [];

    private ?\NumberFormatter $quantityFormatter = null;

    #[AsTwigFilter('decimal')]
    public function decimal(float|int|null $value, int $decimals = 0): string
    {
        return $this->number((float) $value, $decimals);
    }

    /**
     * A coin quantity: fewer decimals as it grows, trailing zeros dropped ("0,184", "3,0612", "1 250").
     */
    #[AsTwigFilter('quantity')]
    public function quantity(float|int|null $value): string
    {
        $value = (float) $value;
        $decimals = match (true) {
            abs($value) >= 1000 => 0,
            abs($value) >= 1 => 4,
            default => 8,
        };

        $formatter = $this->quantityFormatter ??= new \NumberFormatter('fr_FR', \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

        return (string) $formatter->format(round($value, $decimals));
    }

    #[AsTwigFilter('amount')]
    public function amount(float|int|null $value, string $symbol, int $decimals = 0): string
    {
        return $this->number((float) $value, $decimals).self::NBSP.$symbol;
    }

    /**
     * A unit price: no cents from 1 000, cents from 1, 4 significant digits below ("3 012 $", "12,35 $", "0,4512 $", "0,000008912 $").
     * The thresholds apply to the rounded value: 999,996 reads "1 000 $", like 1 000.
     */
    #[AsTwigFilter('price')]
    public function price(float|int|null $value, string $symbol): string
    {
        $value = (float) $value;
        $abs = abs($value);
        $decimals = match (true) {
            round($abs) >= 1000 => 0,
            round($abs, 2) >= 1 => 2,
            0.0 === $abs => 2,
            default => max(4, 3 - (int) floor(log10($abs))),
        };

        return $this->amount($value, $symbol, $decimals);
    }

    #[AsTwigFilter('signed_amount')]
    public function signedAmount(float|int|null $value, string $symbol, int $decimals = 0): string
    {
        return $this->sign((float) $value, $decimals).$this->amount(abs((float) $value), $symbol, $decimals);
    }

    #[AsTwigFilter('percent')]
    public function percent(float|int|null $value, int $decimals = 1): string
    {
        return $this->number((float) $value, $decimals).self::NBSP.'%';
    }

    #[AsTwigFilter('signed_percent')]
    public function signedPercent(float|int|null $value, int $decimals = 1): string
    {
        return $this->sign((float) $value, $decimals).$this->percent(abs((float) $value), $decimals);
    }

    private function number(float $value, int $decimals): string
    {
        $formatter = $this->formatters[$decimals] ??= new \NumberFormatter('fr_FR', \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);

        return (string) $formatter->format(round($value, $decimals));
    }

    /**
     * No sign on a value that rounds to zero: "+0 €" or "−0 €" would read as a change.
     */
    private function sign(float $value, int $decimals): string
    {
        return match (round($value, $decimals) <=> 0.0) {
            1 => '+',
            -1 => self::MINUS,
            default => '',
        };
    }
}
