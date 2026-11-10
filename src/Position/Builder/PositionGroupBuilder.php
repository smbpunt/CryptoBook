<?php

namespace App\Position\Builder;

use App\Position\Dto\PositionGroup;
use App\Position\Entity\Position;

final readonly class PositionGroupBuilder
{
    /**
     * Groups the positions by coin, in the order of the list.
     *
     * @param list<Position> $positions
     *
     * @return list<PositionGroup>
     */
    public function build(array $positions): array
    {
        $byCoin = [];
        foreach ($positions as $position) {
            $byCoin[$position->getCoin()?->getId()][] = $position;
        }

        return array_values(array_map($this->group(...), $byCoin));
    }

    /**
     * @param non-empty-list<Position> $positions
     */
    private function group(array $positions): PositionGroup
    {
        $quantity = 0.0;
        foreach ($positions as $position) {
            $quantity += $position->getRemainingCoins();
        }

        // Average cost weighted by the coins still held.
        $averageCost = 0.0;
        if ($quantity > 0) {
            foreach ($positions as $position) {
                // Raw entry price: getEntryCoinValue() rounds to the cent, 0 for the coins below.
                $averageCost += $position->getEntryCost() / $position->getNbCoins() * $position->getRemainingCoins() / $quantity;
            }
        }

        return new PositionGroup($positions[0]->getCoin(), $quantity, $averageCost, $positions);
    }
}
