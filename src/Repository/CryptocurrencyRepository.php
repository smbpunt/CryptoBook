<?php

namespace App\Repository;

use App\Entity\Cryptocurrency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Cryptocurrency>
 *
 * @method Cryptocurrency|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cryptocurrency|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cryptocurrency[]    findAll()
 * @method Cryptocurrency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CryptocurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cryptocurrency::class);
    }

    public function add(Cryptocurrency $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cryptocurrency $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getSumCoinByUser(UserInterface $user, bool $isStable = false): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.positions', 'p')
            ->andWhere('p.owner = :user')
            ->andWhere('c.isStable = :isStable')
            ->andWhere('p.isOpened = true')
            ->setParameter('user', $user)
            ->setParameter('isStable', $isStable)
            ->select('SUM(p.remainingCoins) as totalsum', 'c as coin')
            ->groupBy('coin')
            ->addOrderBy('c.mcapUsd', 'DESC')
            ->getQuery()->getResult();
    }

//    /**
//     * @return Cryptocurrency[] Returns an array of Cryptocurrency objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Cryptocurrency
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
