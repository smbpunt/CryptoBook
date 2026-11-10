<?php

namespace App\Repository;

use App\Entity\Deposit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Deposit>
 *
 * @method Deposit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deposit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deposit[]    findAll()
 * @method Deposit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepositRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deposit::class);
    }

    public function add(Deposit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Deposit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Deposits of the user with their currency, in one query.
     *
     * @return list<Deposit>
     */
    public function findByOwnerWithCurrency(User $owner): array
    {
        return $this->createQueryBuilder('d')
            ->addSelect('f')
            ->join('d.fiatCurrency', 'f')
            ->where('d.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()->getResult();
    }

    public function getTotal(UserInterface $user): float
    {
        $qb = $this->createQueryBuilder('d')
            ->select('SUM(d.amount) as totalsum')
            ->where('d.owner = :user')
            ->groupBy('d.owner')
            ->setParameter('user', $user);
        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (\Exception) {
        }

        return 0;
    }

    //    /**
    //     * @return Deposit[] Returns an array of Deposit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Deposit
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
