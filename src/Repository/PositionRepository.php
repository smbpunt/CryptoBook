<?php

namespace App\Repository;

use App\Entity\Cryptocurrency;
use App\Entity\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * Quantity of each coin held in the user's open positions, crypto and stable together.
     *
     * @return list<array{totalsum: float|string, symbol: string, libelle: string, color: ?string, priceUsd: float, urlImgThumb: ?string, isStable: bool}>
     */
    public function getHoldingsByUser(UserInterface $user): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.coin', 'c')
            ->andWhere('p.owner = :user')
            ->andWhere('p.isOpened = TRUE')
            ->setParameter('user', $user)
            ->select('SUM(p.remainingCoins) as totalsum', 'c.symbol', 'c.libelle', 'c.color', 'c.priceUsd', 'c.urlImgThumb', 'c.isStable')
            ->groupBy('c.symbol', 'c.libelle', 'c.color', 'c.priceUsd', 'c.urlImgThumb', 'c.isStable')
            ->getQuery()->getArrayResult();
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
