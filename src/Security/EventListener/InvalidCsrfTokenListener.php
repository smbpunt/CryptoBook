<?php

namespace App\Security\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

/**
 * #[IsCsrfTokenValid] throws an authentication exception, which the firewall turns into a
 * redirect to the login page even for a logged-in user. Answer a plain 403 instead.
 *
 * Runs before the firewall exception listener (priority 1). The original exception is not
 * kept as previous: the firewall walks the previous chain and would catch it again.
 */
final class InvalidCsrfTokenListener
{
    #[AsEventListener(event: KernelEvents::EXCEPTION, priority: 2)]
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getThrowable() instanceof InvalidCsrfTokenException) {
            $event->setThrowable(new AccessDeniedHttpException('Invalid CSRF token.'));
        }
    }
}
