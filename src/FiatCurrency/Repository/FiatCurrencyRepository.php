<?php

namespace App\FiatCurrency\Repository;

use App\FiatCurrency\Entity\FiatCurrency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FiatCurrency>
 *
 * @method FiatCurrency|null find($id, $lockMode = null, $lockVersion = null)
 * @method FiatCurrency|null findOneBy(array $criteria, array $orderBy = null)
 * @method FiatCurrency[]    findAll()
 * @method FiatCurrency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FiatCurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FiatCurrency::class);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * The USD currency, seeded by a migration.
     */
    public function getUsd(): FiatCurrency
    {
        return $this->getByFixerKey(FiatCurrency::KEY_USD);
    }

    public function getByFixerKey(string $fixerKey): FiatCurrency
    {
        return $this->findOneBy(['fixerKey' => $fixerKey])
            ?? throw new \LogicException(\sprintf('The %s currency is missing.', $fixerKey));
    }
}
