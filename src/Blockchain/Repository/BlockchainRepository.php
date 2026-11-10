<?php

namespace App\Blockchain\Repository;

use App\Blockchain\Entity\Blockchain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Blockchain>
 *
 * @method Blockchain|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blockchain|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blockchain[]    findAll()
 * @method Blockchain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlockchainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blockchain::class);
    }
}
