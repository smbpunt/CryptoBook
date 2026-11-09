<?php

namespace App\EventListener;

use App\Entity\Cryptocurrency;
use App\Service\CryptocurrencyService;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CryptocurrencyListener
{
    public function __construct(private readonly CryptocurrencyService $cryptocurrencyService)
    {
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Cryptocurrency || null !== $entity->getLibelle()) {
            return;
        }

        $this->cryptocurrencyService->updateDatas($entity);
    }
}
