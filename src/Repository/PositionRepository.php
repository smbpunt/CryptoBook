<?php

namespace App\Repository;

use App\Entity\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
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

    public function getSumCoinByUser(UserInterface $user): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.coin', 'c')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->select('SUM(p.remainingCoins) as totalsum', 'c.libelle', 'c.priceUsd', 'c.priceEur')
            ->groupBy('p.coin')
            ->getQuery()->getArrayResult();
    }

    public function getPositions(UserInterface $user, bool $isStable): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.coin', 'c')
            ->where('p.user = :user')
            ->andWhere('c.isStable = :isStable')
            ->setParameter('user', $user)
            ->setParameter('isStable', $isStable)
            ->addOrderBy('c.mcapUsd', 'DESC')
            ->addOrderBy('p.openedAt', 'ASC')
            ->addOrderBy('p.entryCost', 'DESC')
            ->getQuery()->getResult();
    }

    // /**
    //  * @return Position[] Returns an array of Position objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Position
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
