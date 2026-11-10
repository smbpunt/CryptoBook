<?php

namespace App\Cryptocurrency\EventListener;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Cryptocurrency\Updater\CoinDataUpdater;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

/**
 * Fills a new coin with its CoinGecko data when it is created without a name.
 */
#[AsEntityListener(event: Events::prePersist, entity: Cryptocurrency::class)]
final readonly class CryptocurrencyListener
{
    public function __construct(private CoinDataUpdater $coinDataUpdater)
    {
    }

    public function prePersist(Cryptocurrency $cryptocurrency): void
    {
        if (null !== $cryptocurrency->getLibelle()) {
            return;
        }

        $this->coinDataUpdater->update($cryptocurrency);
    }
}
