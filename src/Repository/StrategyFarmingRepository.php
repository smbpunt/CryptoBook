<?php

namespace App\Repository;

use App\Entity\StrategyFarming;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StrategyFarming|null find($id, $lockMode = null, $lockVersion = null)
 * @method StrategyFarming|null findOneBy(array $criteria, array $orderBy = null)
 * @method StrategyFarming[]    findAll()
 * @method StrategyFarming[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StrategyFarmingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StrategyFarming::class);
    }

    // /**
    //  * @return StrategyFarming[] Returns an array of StrategyFarming objects
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
    public function findOneBySomeField($value): ?StrategyFarming
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
