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
     * @return list<int>
     */
    public function findOwnerIdsRecordedAt(\DateTimeImmutable $recordedAt): array
    {
        return array_map(intval(...), $this->createQueryBuilder('s')
            ->select('IDENTITY(s.owner)')
            ->andWhere('s.recordedAt = :recordedAt')
            ->setParameter('recordedAt', $recordedAt, 'datetimetz_immutable')
            ->getQuery()
            ->getSingleColumnResult());
    }
}
