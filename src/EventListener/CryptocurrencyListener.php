<?php

namespace App\EventListener;

use App\Entity\Cryptocurrency;
use App\Service\CryptocurrencyService;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CryptocurrencyListener
{
    private CryptocurrencyService $cryptocurrencyService;

    /**
     * @param CryptocurrencyService $cryptocurrencyService
     */
    public function __construct(CryptocurrencyService $cryptocurrencyService)
    {
        $this->cryptocurrencyService = $cryptocurrencyService;
    }


    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if(!$entity instanceof Cryptocurrency || $entity->getLibelle() !== null) {
            return;
        }

        $this->cryptocurrencyService->updateDatas($entity);
    }
}