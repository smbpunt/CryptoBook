<?php

namespace App\Strategy\Dca\Repository;

use App\Strategy\Dca\Entity\CoinPercentDca;
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
}
