<?php

namespace App\Events;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Cryptocurrency;
use App\Service\CryptocurrencyService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CryptocurrencySubscriber implements EventSubscriberInterface
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
            KernelEvents::VIEW => ['getDatasFromCoingecko', EventPriorities::PRE_WRITE]
        ];
    }

    public function getDatasFromCoingecko(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if($result instanceof Cryptocurrency && $method === "POST") {
            $this->cryptocurrencyService->updateDatas($result);
        }
    }
}