<?php

namespace App\Strategy\Dca\Repository;

use App\Strategy\Dca\Entity\CoinPercentDca;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoinPercentDca>
 */
class CoinPercentDcaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoinPercentDca::class);
    }
}
