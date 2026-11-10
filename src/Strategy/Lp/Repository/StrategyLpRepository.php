<?php

namespace App\Strategy\Lp\Repository;

use App\Strategy\Lp\Entity\StrategyLp;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StrategyLp>
 *
 * @method StrategyLp|null find($id, $lockMode = null, $lockVersion = null)
 * @method StrategyLp|null findOneBy(array $criteria, array $orderBy = null)
 * @method StrategyLp[]    findAll()
 * @method StrategyLp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StrategyLpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StrategyLp::class);
    }

    public function add(StrategyLp $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Liquidity pools of the user, with their coins.
     *
     * @return list<StrategyLp>
     */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('l')
            ->addSelect('c1', 'c2')
            ->join('l.coin1', 'c1')
            ->join('l.coin2', 'c2')
            ->where('l.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()->getResult();
    }

    public function remove(StrategyLp $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
