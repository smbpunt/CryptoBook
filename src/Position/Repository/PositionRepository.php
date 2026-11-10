<?php

namespace App\Position\Repository;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Position\Dto\CoinQuantity;
use App\Position\Entity\Position;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Position>
 *
 * @method Position|null find($id, $lockMode = null, $lockVersion = null)
 * @method Position|null findOneBy(array $criteria, array $orderBy = null)
 * @method Position[]    findAll()
 * @method Position[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Position::class);
    }

    public function add(Position $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Position $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Quantity of every coin held by each owner, in a single query. Closed positions count for 0,
     * so an owner whose positions are all closed still gets rows.
     *
     * @return list<CoinQuantity>
     */
    public function sumQuantitiesByOwnerAndCoin(?User $owner = null): array
    {
        // Coin as root alias: Doctrine only hydrates an entity selected from the root.
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from(Cryptocurrency::class, 'c')
            ->join(Position::class, 'p', Join::ON, 'p.coin = c')
            ->select('c', 'IDENTITY(p.owner) AS ownerId', 'SUM(CASE WHEN p.isOpened = TRUE THEN p.remainingCoins ELSE 0 END) AS quantity')
            ->groupBy('p.owner', 'c.id');

        if ($owner instanceof User) {
            $qb->andWhere('p.owner = :owner')
                ->setParameter('owner', $owner);
        }

        return array_map(static fn (array $row): CoinQuantity => new CoinQuantity(
            $row[0],
            (int) $row['ownerId'],
            (float) $row['quantity'],
        ), $qb->getQuery()->getResult());
    }

    /**
     * Open positions of the user, with their coin, biggest market cap first.
     *
     * @return list<Position>
     */
    public function findOpen(User $owner, bool $stable): array
    {
        return $this->createOwnedQueryBuilder($owner)
            ->andWhere('p.isOpened = true')
            ->andWhere('c.isStable = :stable')
            ->setParameter('stable', $stable)
            ->getQuery()->getResult();
    }

    /**
     * Closed positions of the user, stable or not, with their coin, biggest market cap first.
     *
     * @return list<Position>
     */
    public function findClosed(User $owner): array
    {
        return $this->createOwnedQueryBuilder($owner)
            ->andWhere('p.isOpened = false')
            ->getQuery()->getResult();
    }

    private function createOwnedQueryBuilder(User $owner): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->addSelect('c')
            ->join('p.coin', 'c')
            ->where('p.owner = :owner')
            ->setParameter('owner', $owner)
            ->addOrderBy('c.mcapUsd', \SortDirection::Descending)
            ->addOrderBy('p.openedAt', \SortDirection::Ascending)
            ->addOrderBy('p.entryCost', \SortDirection::Descending);
    }
}
