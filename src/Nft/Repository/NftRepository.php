<?php

namespace App\Nft\Repository;

use App\Nft\Entity\Nft;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Nft>
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
}
