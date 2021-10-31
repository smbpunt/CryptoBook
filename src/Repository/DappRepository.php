<?php

namespace App\Repository;

use App\Entity\Dapp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Dapp|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dapp|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dapp[]    findAll()
 * @method Dapp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DappRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dapp::class);
    }

    // /**
    //  * @return Dapp[] Returns an array of Dapp objects
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
    public function findOneBySomeField($value): ?Dapp
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
