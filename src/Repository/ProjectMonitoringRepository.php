<?php

namespace App\Repository;

use App\Entity\ProjectMonitoring;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjectMonitoring|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectMonitoring|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectMonitoring[]    findAll()
 * @method ProjectMonitoring[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectMonitoringRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectMonitoring::class);
    }

    // /**
    //  * @return ProjectMonitoring[] Returns an array of ProjectMonitoring objects
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
    public function findOneBySomeField($value): ?ProjectMonitoring
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
