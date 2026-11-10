<?php

namespace App\Repository;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Entity\Position;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @return list<array{0: Cryptocurrency, ownerId: int, quantity: float}>
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

        return array_map(static fn (array $row): array => [
            0 => $row[0],
            'ownerId' => (int) $row['ownerId'],
            'quantity' => (float) $row['quantity'],
        ], $qb->getQuery()->getResult());
    }

    /**
     * @return Position[]
     */
    public function getPositions(UserInterface $user, bool $isStable, bool $isOpen = true, ?Cryptocurrency $cryptocurrency = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.coin', 'c')
            ->where('p.owner = :user')
            ->andWhere('c.isStable = :isStable')
            ->andWhere('p.isOpened = :isOpen')
            ->setParameter('user', $user)
            ->setParameter('isStable', $isStable)
            ->setParameter('isOpen', $isOpen)
            ->addOrderBy('c.mcapUsd', \SortDirection::Descending)
            ->addOrderBy('p.openedAt', \SortDirection::Ascending)
            ->addOrderBy('p.entryCost', \SortDirection::Descending);

        if ($cryptocurrency) {
            $qb->andWhere('p.coin = :coin')
                ->setParameter('coin', $cryptocurrency);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Position[] Returns an array of Position objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Position
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
