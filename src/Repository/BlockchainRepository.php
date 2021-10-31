<?php

namespace App\Repository;

use App\Entity\Blockchain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Blockchain|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blockchain|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blockchain[]    findAll()
 * @method Blockchain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlockchainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blockchain::class);
    }

    // /**
    //  * @return Blockchain[] Returns an array of Blockchain objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Blockchain
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
