<?php

namespace App\Position\Repository;

use App\Position\Entity\StrategyPosition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StrategyPosition>
 *
 * @method StrategyPosition|null find($id, $lockMode = null, $lockVersion = null)
 * @method StrategyPosition|null findOneBy(array $criteria, array $orderBy = null)
 * @method StrategyPosition[]    findAll()
 * @method StrategyPosition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StrategyPositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StrategyPosition::class);
    }
}
