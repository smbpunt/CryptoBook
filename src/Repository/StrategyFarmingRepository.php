<?php

namespace App\Repository;

use App\Entity\StrategyFarming;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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

    /**
     * @param UserInterface $user
     * @param bool $isStable
     * @return StrategyFarming[]
     */
    public function findByStable(UserInterface $user, bool $isStable): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.coin', 'c')
            ->where('f.user = :user')
            ->andWhere('c.isStable = :isStable')
            ->setParameter('user', $user)
            ->setParameter('isStable', $isStable)
            ->getQuery()->getResult();
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
