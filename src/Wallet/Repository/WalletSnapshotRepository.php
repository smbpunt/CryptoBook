<?php

namespace App\Wallet\Repository;

use App\User\Entity\User;
use App\Wallet\Dto\SnapshotPoint;
use App\Wallet\Dto\WalletSnapshotStats;
use App\Wallet\Entity\WalletSnapshot;
use App\Wallet\Enum\HistoryPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Snapshots of the period, oldest first: every one for an hourly period, else the last one of each UTC day.
     *
     * @return list<SnapshotPoint>
     */
    public function findPoints(User $owner, HistoryPeriod $period, \DateTimeImmutable $now): array
    {
        $since = $period->since($now);

        if ($period->isHourly()) {
            $qb = $this->createQueryBuilder('s')
                ->select(\sprintf('NEW %s(s.recordedAt, s.valueUsd)', SnapshotPoint::class))
                ->andWhere('s.owner = :owner')
                ->setParameter('owner', $owner)
                ->orderBy('s.recordedAt', \SortDirection::Ascending);
            if (null !== $since) {
                $qb->andWhere('s.recordedAt >= :since')->setParameter('since', $since, Types::DATETIMETZ_IMMUTABLE);
            }

            return $qb->getQuery()->getResult();
        }

        $connection = $this->getEntityManager()->getConnection();
        $rows = $connection->fetchAllAssociative(
            'SELECT recorded_at, value_usd FROM (
                SELECT DISTINCT ON (date_trunc(\'day\', recorded_at AT TIME ZONE \'UTC\')) recorded_at, value_usd
                FROM wallet_snapshot
                WHERE owner_id = :owner'.(null !== $since ? ' AND recorded_at >= :since' : '').'
                ORDER BY date_trunc(\'day\', recorded_at AT TIME ZONE \'UTC\'), recorded_at DESC
            ) last_of_day
            ORDER BY recorded_at',
            array_filter(['owner' => $owner->getId(), 'since' => $since], static fn (mixed $value): bool => null !== $value),
            ['since' => Types::DATETIMETZ_IMMUTABLE],
        );
        $platform = $connection->getDatabasePlatform();
        $dateType = Type::getType(Types::DATETIMETZ_IMMUTABLE);

        return array_map(static fn (array $row): SnapshotPoint => new SnapshotPoint(
            $dateType->convertToPHPValue($row['recorded_at'], $platform),
            (float) $row['value_usd'],
        ), $rows);
    }

    public function findStats(User $owner, \DateTimeImmutable $now): WalletSnapshotStats
    {
        $high = $this->createOwnerQuery($owner)
            ->orderBy('s.valueUsd', \SortDirection::Descending)
            ->addOrderBy('s.recordedAt', \SortDirection::Descending)
            ->getQuery()->getOneOrNullResult();
        if (null === $high) {
            return new WalletSnapshotStats();
        }

        $dayAgo = $this->createOwnerQuery($owner)
            ->andWhere('s.recordedAt BETWEEN :from AND :to')
            ->setParameter('from', $now->modify('-48 hours'), Types::DATETIMETZ_IMMUTABLE)
            ->setParameter('to', $now->modify('-24 hours'), Types::DATETIMETZ_IMMUTABLE)
            ->orderBy('s.recordedAt', \SortDirection::Descending)
            ->getQuery()->getOneOrNullResult();

        $last = $this->createOwnerQuery($owner)
            ->orderBy('s.recordedAt', \SortDirection::Descending)
            ->getQuery()->getOneOrNullResult();

        return new WalletSnapshotStats($high->getValueUsd(), $high->getRecordedAt(), $dayAgo?->getValueUsd(), $last?->getRecordedAt());
    }

    private function createOwnerQuery(User $owner): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.owner = :owner')
            ->setParameter('owner', $owner)
            ->setMaxResults(1);
    }
}
