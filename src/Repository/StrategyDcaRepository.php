<?php

namespace App\Repository;

use App\Entity\StrategyDca;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StrategyDca|null find($id, $lockMode = null, $lockVersion = null)
 * @method StrategyDca|null findOneBy(array $criteria, array $orderBy = null)
 * @method StrategyDca[]    findAll()
 * @method StrategyDca[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StrategyDcaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StrategyDca::class);
    }

    // /**
    //  * @return StrategyDca[] Returns an array of StrategyDca objects
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
    public function findOneBySomeField($value): ?StrategyDca
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
