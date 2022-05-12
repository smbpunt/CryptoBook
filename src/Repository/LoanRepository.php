<?php

namespace App\Repository;

use App\Entity\Loan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Loan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Loan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Loan[]    findAll()
 * @method Loan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    public function getTotal(UserInterface $user): float
    {
        $qb = $this->createQueryBuilder('l')
            ->select('SUM(l.nbCoins * c.priceUsd) as totalsum')
            ->join('l.coin', 'c')
            ->where('l.user = :user')
            ->groupBy('l.user')
            ->setParameter('user', $user);
        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (Exception $e) {
        }
        return 0;
    }
}
