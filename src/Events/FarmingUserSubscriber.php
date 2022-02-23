<?php

namespace App\Events;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Position;
use App\Entity\StrategyFarming;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class FarmingUserSubscriber implements EventSubscriberInterface
{
    private Security $security;

    /**
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setUserForFarming', EventPriorities::PRE_VALIDATE]
        ];
    }

    public function setUserForFarming(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if($result instanceof StrategyFarming && $method === "POST") {
            $user = $this->security->getUser();
            if($user) {
                $result->setUser($user);
            }
        }
    }
}