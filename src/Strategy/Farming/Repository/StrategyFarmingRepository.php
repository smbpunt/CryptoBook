<?php

namespace App\Strategy\Farming\Repository;

use App\Strategy\Farming\Entity\StrategyFarming;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StrategyFarming>
 *
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

    public function add(StrategyFarming $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StrategyFarming $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return StrategyFarming[]
     */
    /**
     * Farmings of the user on stable coins or not, with their coin.
     *
     * @return list<StrategyFarming>
     */
    public function findByOwner(User $owner, bool $stable): array
    {
        return $this->createQueryBuilder('f')
            ->addSelect('c')
            ->join('f.coin', 'c')
            ->where('f.owner = :owner')
            ->andWhere('c.isStable = :stable')
            ->setParameter('owner', $owner)
            ->setParameter('stable', $stable)
            ->getQuery()->getResult();
    }

    //    /**
    //     * @return StrategyFarming[] Returns an array of StrategyFarming objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?StrategyFarming
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
