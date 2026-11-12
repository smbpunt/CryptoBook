<?php

namespace App\Loan\Repository;

use App\Loan\Entity\Loan;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Loan>
 */
class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    public function add(Loan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Loan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Current value of the loans of the user, in USD.
     */
    public function sumValueUsd(User $owner): float
    {
        return (float) $this->createQueryBuilder('l')
            ->select('COALESCE(SUM(l.nbCoins * c.priceUsd), 0)')
            ->join('l.coin', 'c')
            ->where('l.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()->getSingleScalarResult();
    }
}
