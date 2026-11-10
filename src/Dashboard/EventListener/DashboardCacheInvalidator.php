<?php

namespace App\Dashboard\EventListener;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Dashboard\Cache\DashboardCache;
use App\Deposit\Entity\Deposit;
use App\FiatCurrency\Entity\FiatCurrency;
use App\Loan\Entity\Loan;
use App\Position\Entity\Position;
use App\Position\Entity\Vente;
use App\Strategy\Farming\Entity\StrategyFarming;
use App\Strategy\Lp\Entity\StrategyLp;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;

/**
 * Invalidates the dashboard cache when the data it reads changes: every user for prices and rates,
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
                $entity instanceof Position, $entity instanceof Deposit, $entity instanceof Loan,
                $entity instanceof StrategyFarming, $entity instanceof StrategyLp => $this->addUser($entity->getOwner()),
                $entity instanceof Vente => $this->addUser($entity->getPosition()?->getOwner()),
                $entity instanceof User && $this->favoriteCurrencyChanged($uow, $entity) => $this->addUser($entity),
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

    /**
     * The dashboard only reads the favorite currency of the user, not its last connection.
     */
    private function favoriteCurrencyChanged(UnitOfWork $uow, User $user): bool
    {
        return \array_key_exists('favoriteFiatCurrency', $uow->getEntityChangeSet($user));
    }
}
