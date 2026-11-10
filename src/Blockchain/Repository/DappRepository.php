<?php

namespace App\Blockchain\Repository;

use App\Blockchain\Entity\Dapp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dapp>
 *
 * @method Dapp|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dapp|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dapp[]    findAll()
 * @method Dapp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DappRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dapp::class);
    }
}
