<?php

namespace App\Repository;

use App\Entity\TypeProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeProject|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeProject|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeProject[]    findAll()
 * @method TypeProject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeProject::class);
    }

    // /**
    //  * @return TypeProject[] Returns an array of TypeProject objects
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
    public function findOneBySomeField($value): ?TypeProject
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
