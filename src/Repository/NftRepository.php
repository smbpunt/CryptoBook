<?php

namespace App\Repository;

use App\Entity\Nft;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Nft>
 *
 * @method Nft|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nft|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nft[]    findAll()
 * @method Nft[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nft::class);
    }

    public function add(Nft $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Nft $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findBySold(UserInterface $user, bool $isSold = false)
    {
        return $this->createQueryBuilder('n')
            ->where('n.owner = :user')
            ->andWhere('n.soldOn IS ' . ($isSold ? 'NOT' : '') . ' NULL')
            ->setParameter('user', $user)
            ->orderBy('n.purchasedOn', 'ASC')
            ->getQuery()->getResult();
    }

    // Return the sum of the current value of all NFTs that are not sold
    public function findCurrentValue(UserInterface $user): float
    {
        return $this->createQueryBuilder('n')
            ->select('SUM(n.currentUsdValue)')
            ->where('n.owner = :user')
            ->andWhere('n.soldOn IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

//    /**
//     * @return Nft[] Returns an array of Nft objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Nft
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
