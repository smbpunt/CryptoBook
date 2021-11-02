<?php

namespace App\Service;

use App\Entity\Position;

class PositionService
{
    public function calculateRemainingCoins(Position $position): void
    {
        if ($position->getVentes()->isEmpty()) {
            $position->setRemainingCoins($position->getNbCoins());
            return;
        }

        $coins = $position->getNbCoins();
        $ventes = $position->getVentesSortedByDate();
        $remainingCoins = $coins;

        foreach ($ventes as $vente) {
            $remainingCoins *= (1 - ($vente->getPercent() / 100));
        }

        $position->setRemainingCoins($remainingCoins);
    }
}