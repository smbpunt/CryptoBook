<?php

namespace App\Nft\Repository;

use App\Entity\User;
use App\Nft\Entity\Nft;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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

    /**
     * NFTs still held by the user, oldest purchase first.
     *
     * @return list<Nft>
     */
    public function findHeld(User $owner): array
    {
        return $this->createOwnedQueryBuilder($owner)
            ->andWhere('n.soldOn IS NULL')
            ->getQuery()->getResult();
    }

    /**
     * NFTs sold by the user, oldest purchase first.
     *
     * @return list<Nft>
     */
    public function findSold(User $owner): array
    {
        return $this->createOwnedQueryBuilder($owner)
            ->andWhere('n.soldOn IS NOT NULL')
            ->getQuery()->getResult();
    }

    private function createOwnedQueryBuilder(User $owner): QueryBuilder
    {
        return $this->createQueryBuilder('n')
            ->where('n.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('n.purchasedOn', \SortDirection::Ascending);
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
