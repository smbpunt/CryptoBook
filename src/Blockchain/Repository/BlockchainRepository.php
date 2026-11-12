<?php

namespace App\Blockchain\Repository;

use App\Blockchain\Entity\Blockchain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Blockchain>
 */
class BlockchainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blockchain::class);
    }
}
