<?php

namespace App\ProjectMonitoring\Repository;

use App\ProjectMonitoring\Entity\ProjectMonitoring;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectMonitoring>
 *
 * @method ProjectMonitoring|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectMonitoring|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectMonitoring[]    findAll()
 * @method ProjectMonitoring[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectMonitoringRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectMonitoring::class);
    }

    public function add(ProjectMonitoring $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProjectMonitoring $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
