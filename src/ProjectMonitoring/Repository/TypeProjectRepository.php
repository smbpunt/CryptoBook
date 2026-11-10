<?php

namespace App\ProjectMonitoring\Repository;

use App\ProjectMonitoring\Entity\TypeProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeProject>
 */
class TypeProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeProject::class);
    }
}
