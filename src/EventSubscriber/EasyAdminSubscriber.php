<?php

namespace App\EventSubscriber;

use App\Entity\Cryptocurrency;
use App\Service\CryptocurrencyService;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private CryptocurrencyService $cryptocurrencyService;

    /**
     * @param CryptocurrencyService $cryptocurrencyService
     */
    public function __construct(CryptocurrencyService $cryptocurrencyService)
    {
        $this->cryptocurrencyService = $cryptocurrencyService;
    }


    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['fetchDatasCryptocurrency']
        ];
    }

    public function fetchDatasCryptocurrency(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Cryptocurrency)) {
            return;
        }

        $this->cryptocurrencyService->updateDatas($entity, false);
    }
}