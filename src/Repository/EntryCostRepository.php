<?php

namespace App\Repository;

use App\Entity\EntryCost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntryCost|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntryCost|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntryCost[]    findAll()
 * @method EntryCost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryCostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryCost::class);
    }

    // /**
    //  * @return EntryCost[] Returns an array of EntryCost objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EntryCost
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
