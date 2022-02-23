<?php

namespace App\Repository;

use App\Entity\TypeCryptocurrency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeCryptocurrency|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeCryptocurrency|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeCryptocurrency[]    findAll()
 * @method TypeCryptocurrency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeCryptocurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeCryptocurrency::class);
    }

    // /**
    //  * @return TypeCryptocurrency[] Returns an array of TypeCryptocurrency objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TypeCryptocurrency
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
