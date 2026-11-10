<?php

namespace App\ProjectMonitoring\Repository;

use App\ProjectMonitoring\Entity\TypeProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeProject>
 *
 * @method TypeProject|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeProject|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeProject[]    findAll()
 * @method TypeProject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeProject::class);
    }
}
