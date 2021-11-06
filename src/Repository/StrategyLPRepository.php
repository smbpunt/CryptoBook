<?php

namespace App\Repository;

use App\Entity\StrategyLP;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StrategyLP|null find($id, $lockMode = null, $lockVersion = null)
 * @method StrategyLP|null findOneBy(array $criteria, array $orderBy = null)
 * @method StrategyLP[]    findAll()
 * @method StrategyLP[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StrategyLPRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StrategyLP::class);
    }

    // /**
    //  * @return StrategyLP[] Returns an array of StrategyLP objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StrategyLP
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
