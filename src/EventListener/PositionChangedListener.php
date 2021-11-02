<?php

namespace App\EventListener;

use App\Entity\Position;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class PositionChangedListener
{
    public function prePersist(Position $position, LifecycleEventArgs $event): void
    {
        $this->calculateRemainingCoins($position);
    }

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

    public function preUpdate(Position $position, LifecycleEventArgs $event): void
    {
        $this->calculateRemainingCoins($position);
    }
}