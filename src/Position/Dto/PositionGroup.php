<?php

namespace App\Position\Dto;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Position\Entity\Position;

/**
 * Open positions of one coin, with the quantity held and its average cost.
 */
final readonly class PositionGroup
{
    /**
     * @param list<Position> $positions
     */
    public function __construct(
        public Cryptocurrency $coin,
        public float $quantity,
        public float $averageCost,
        public array $positions,
    ) {
    }

    public function valueUsd(): float
    {
        return $this->quantity * ($this->coin->getPriceUsd() ?? 0.0);
    }

    /**
     * Evolution of the price against the average cost, null when the coins cost nothing.
     */
    public function evolutionPercent(): ?float
    {
        if (0.0 === $this->averageCost) {
            return null;
        }

        return ($this->coin->getPriceUsd() ?? 0.0) * 100 / $this->averageCost - 100;
    }
}
