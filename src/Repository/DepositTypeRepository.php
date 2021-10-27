<?php

namespace App\Repository;

use App\Entity\DepositType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DepositType|null find($id, $lockMode = null, $lockVersion = null)
 * @method DepositType|null findOneBy(array $criteria, array $orderBy = null)
 * @method DepositType[]    findAll()
 * @method DepositType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepositTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepositType::class);
    }

    // /**
    //  * @return DepositType[] Returns an array of DepositType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DepositType
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
