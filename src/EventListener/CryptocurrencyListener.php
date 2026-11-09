<?php

namespace App\EventListener;

use App\Entity\Cryptocurrency;
use App\Service\CryptocurrencyService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

/**
 * Fills a new coin with its CoinGecko data when it is created without a name.
 */
#[AsEntityListener(event: Events::prePersist, entity: Cryptocurrency::class)]
final readonly class CryptocurrencyListener
{
    public function __construct(private CryptocurrencyService $cryptocurrencyService)
    {
    }

    public function prePersist(Cryptocurrency $cryptocurrency): void
    {
        if (null !== $cryptocurrency->getLibelle()) {
            return;
        }

        $this->cryptocurrencyService->updateDatas($cryptocurrency);
    }
}
