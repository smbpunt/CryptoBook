<?php

namespace App\Dashboard\EventListener;

use App\Dashboard\Cache\DashboardCache;
use App\Entity\Cryptocurrency;
use App\Entity\FiatCurrency;
use App\Entity\OwnedInterface;
use App\Entity\User;
use App\Entity\Vente;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Invalidates the dashboard cache when its data changes: every user for prices and rates,
 * the owner for its own data. Catches the commands, the back-office and the forms alike.
 */
#[AsDoctrineListener(event: Events::onFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
final class DashboardCacheInvalidator
{
    private bool $all = false;

    /** @var array<int, true> */
    private array $userIds = [];

    public function __construct(private readonly DashboardCache $dashboardCache)
    {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        // A failed flush never reaches postFlush: start clean.
        $this->all = false;
        $this->userIds = [];

        $uow = $args->getObjectManager()->getUnitOfWork();
        $entities = [...$uow->getScheduledEntityInsertions(), ...$uow->getScheduledEntityUpdates(), ...$uow->getScheduledEntityDeletions()];

        foreach ($entities as $entity) {
            match (true) {
                $entity instanceof Cryptocurrency, $entity instanceof FiatCurrency => $this->all = true,
                $entity instanceof OwnedInterface => $this->addUser($entity->getOwner()),
                $entity instanceof Vente => $this->addUser($entity->getPosition()?->getOwner()),
                $entity instanceof User => $this->addUser($entity),
                default => null,
            };
        }
    }

    public function postFlush(): void
    {
        if ($this->all) {
            $this->dashboardCache->invalidateAll();
        } else {
            $this->dashboardCache->invalidateUsers(array_keys($this->userIds));
        }

        $this->all = false;
        $this->userIds = [];
    }

    private function addUser(?User $user): void
    {
        if (null !== $user?->getId()) {
            $this->userIds[$user->getId()] = true;
        }
    }
}
