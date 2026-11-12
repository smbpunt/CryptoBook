<?php

namespace App\Deposit\Repository;

use App\Deposit\Entity\Deposit;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deposit>
 */
class DepositRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deposit::class);
    }

    public function add(Deposit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Deposit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Deposits of the user with their currency, in one query, oldest first.
     *
     * @return list<Deposit>
     */
    public function findByOwnerWithCurrency(User $owner): array
    {
        return $this->createQueryBuilder('d')
            ->addSelect('f')
            ->join('d.fiatCurrency', 'f')
            ->where('d.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('d.depositedAt', \SortDirection::Ascending)
            ->getQuery()->getResult();
    }
}
