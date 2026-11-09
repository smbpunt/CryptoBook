<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class AuthenticationSubscriber
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if ($user instanceof User) {
            $user->setLastConnectedAt(new \DateTimeImmutable());
            $this->userRepository->add($user, true);
        }
    }
}
