<?php

namespace App\User\EventListener;

use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Records the date of the last login.
 */
#[AsEventListener]
final readonly class LastConnectionListener
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if ($user instanceof User) {
            $user->setLastConnectedAt(new \DateTimeImmutable());
            $this->userRepository->add($user, true);
        }
    }
}
