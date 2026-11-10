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

    #[AsTwigFilter('decimal')]
    public function decimal(float|int|null $value, int $decimals = 0): string
    {
        return $this->number((float) $value, $decimals);
    }

    #[AsTwigFilter('amount')]
    public function amount(float|int|null $value, string $symbol, int $decimals = 0): string
    {
        return $this->number((float) $value, $decimals).self::NBSP.$symbol;
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
