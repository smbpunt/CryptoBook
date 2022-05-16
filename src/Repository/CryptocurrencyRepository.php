<?php

namespace App\Repository;

use App\Entity\Cryptocurrency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
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


    public function getSumCoinByUser(UserInterface $user, bool $isStable = false): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.positions', 'p')
            ->andWhere('p.user = :user')
            ->andWhere('c.isStable = :isStable')
            ->andWhere('p.isOpened = 1')
            ->setParameter('user', $user)
            ->setParameter('isStable', $isStable)
            ->select('SUM(p.remainingCoins) as totalsum', 'c as coin')
            ->groupBy('p.coin')
            ->getQuery()->getResult();
    }
}
