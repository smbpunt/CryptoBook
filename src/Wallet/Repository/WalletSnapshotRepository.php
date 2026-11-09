<?php

namespace App\Wallet\Repository;

use App\Wallet\Entity\WalletSnapshot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WalletSnapshot>
 */
class WalletSnapshotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WalletSnapshot::class);
    }

    /**
     * @return array<int, WalletSnapshot> snapshots taken at this date, indexed by owner id
     */
    public function findByTakenAtIndexedByOwner(\DateTimeImmutable $takenAt): array
    {
        $snapshots = [];
        foreach ($this->findBy(['takenAt' => $takenAt]) as $snapshot) {
            $snapshots[(int) $snapshot->getOwner()->getId()] = $snapshot;
        }

        return $snapshots;
    }
}
