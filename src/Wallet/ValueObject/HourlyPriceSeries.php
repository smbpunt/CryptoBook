<?php

namespace App\Wallet\ValueObject;

/**
 * USD prices of a coin, one per hour. A missing hour takes the last known price before it.
 */
final readonly class HourlyPriceSeries
{
    /**
     * @param array<int, float> $prices by hour timestamp, sorted
     */
    private function __construct(private array $prices)
    {
    }

    /**
     * Each point goes to its nearest hour; the last point of an hour wins.
     *
     * @param array<int, float> $points price by Unix timestamp in seconds
     */
    public static function fromPoints(array $points): self
    {
        $prices = [];
        foreach ($points as $timestamp => $price) {
            $prices[intdiv($timestamp + 1800, 3600) * 3600] = $price;
        }
        ksort($prices);

        return new self($prices);
    }

    /**
     * Price of the hour, else of the last known hour before it, else of the first point. Null for an empty series.
     */
    public function at(\DateTimeImmutable $hour): ?float
    {
        $timestamp = $hour->getTimestamp();
        if (isset($this->prices[$timestamp])) {
            return $this->prices[$timestamp];
        }

        $price = null;
        foreach ($this->prices as $pointTimestamp => $pointPrice) {
            if (null !== $price && $pointTimestamp > $timestamp) {
                break;
            }
            $price = $pointPrice;
        }

        return $price;
    }
}
