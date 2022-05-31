<?php

namespace App\Repository;

use App\Entity\CoinPercentDca;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoinPercentDca>
 *
 * @method CoinPercentDca|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoinPercentDca|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoinPercentDca[]    findAll()
 * @method CoinPercentDca[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoinPercentDcaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoinPercentDca::class);
    }

    public function add(CoinPercentDca $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CoinPercentDca $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CoinPercentDca[] Returns an array of CoinPercentDca objects
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

//    public function findOneBySomeField($value): ?CoinPercentDca
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
