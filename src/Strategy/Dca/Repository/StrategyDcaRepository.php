<?php

namespace App\Strategy\Dca\Repository;

use App\Strategy\Dca\Entity\StrategyDca;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StrategyDca>
 *
 * @method StrategyDca|null find($id, $lockMode = null, $lockVersion = null)
 * @method StrategyDca|null findOneBy(array $criteria, array $orderBy = null)
 * @method StrategyDca[]    findAll()
 * @method StrategyDca[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StrategyDcaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StrategyDca::class);
    }

    public function add(StrategyDca $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByOwner(User $owner): ?StrategyDca
    {
        return $this->findOneBy(['owner' => $owner]);
    }
}
