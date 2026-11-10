<?php

namespace App\Tests\Dashboard\Cache;

use App\Dashboard\Cache\DashboardCache;
use App\Position\Entity\Vente;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The cache and its invalidation by App\Dashboard\EventListener\DashboardCacheInvalidator.
 */
final class DashboardCacheTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private Dataset $data;
    private DashboardCache $cache;

    protected function setUp(): void
    {
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->data = Dataset::load($this->em);
        $this->cache = self::getContainer()->get(DashboardCache::class);
    }

    public function testServesTheCachedDataUntilAFlush(): void
    {
        self::assertSame(26000.0, $this->cache->get($this->data->user)->totalUsd);

        // Changed in memory only: a rebuild would see it, the cache does not.
        $this->data->btc->setPriceUsd(60000);
        self::assertSame(26000.0, $this->cache->get($this->data->user)->totalUsd);

        $this->em->flush();
        self::assertSame(31000.0, $this->cache->get($this->data->user)->totalUsd);
    }

    public function testPriceChangeInvalidatesEveryUser(): void
    {
        $this->warmUp();

        $this->data->btc->setPriceUsd(60000);
        $this->em->flush();

        self::assertFalse($this->isCached($this->data->user->getId()));
        self::assertFalse($this->isCached($this->data->admin->getId()));
    }

    public function testRateChangeInvalidatesEveryUser(): void
    {
        $this->warmUp();

        $this->data->eur->setRates(['USD' => 2.0]);
        $this->em->flush();

        self::assertFalse($this->isCached($this->data->user->getId()));
        self::assertFalse($this->isCached($this->data->admin->getId()));
    }

    public function testOwnDataChangeInvalidatesOnlyItsOwner(): void
    {
        $this->warmUp();

        $this->data->deposit->setAmount(2000);
        $this->em->flush();

        self::assertFalse($this->isCached($this->data->user->getId()));
        self::assertTrue($this->isCached($this->data->admin->getId()));
    }

    public function testDeletionInvalidatesItsOwner(): void
    {
        self::assertEqualsWithDelta(1073.941, $this->cache->get($this->data->user)->depositUsd, 1e-9);

        $this->em->remove($this->data->deposit);
        $this->em->flush();

        self::assertSame(0.0, $this->cache->get($this->data->user)->depositUsd);
    }

    public function testSaleInvalidatesThePositionOwner(): void
    {
        $this->warmUp();

        $this->em->persist(new Vente()->setPosition($this->data->position)->setPercent(50)->setPriceSold(60000)
            ->setSoldAt(new \DateTimeImmutable('2024-06-01')));
        $this->em->flush();

        self::assertFalse($this->isCached($this->data->user->getId()));
        self::assertTrue($this->isCached($this->data->admin->getId()));
    }

    public function testSaleRemovedFromThePositionInvalidatesItsOwner(): void
    {
        $sale = new Vente()->setPercent(50)->setPriceSold(60000)->setSoldAt(new \DateTimeImmutable('2024-06-01'));
        $this->data->position->addVente($sale);
        $this->em->flush();
        $this->warmUp();

        // As the Position form does: the sale is detached, then deleted by orphanRemoval. Its position is
        // null by then, the owner is found through the Position, updated by its PreFlush on remainingCoins.
        $this->data->position->removeVente($sale);
        $this->em->flush();

        self::assertFalse($this->isCached($this->data->user->getId()));
        self::assertTrue($this->isCached($this->data->admin->getId()));
    }

    public function testFavoriteCurrencyChangeInvalidatesTheUser(): void
    {
        $this->warmUp();

        $this->data->user->setFavoriteFiatCurrency($this->data->usd);
        $this->em->flush();

        self::assertSame('$', $this->cache->get($this->data->user)->currencySymbol);
        self::assertTrue($this->isCached($this->data->admin->getId()));
    }

    private function warmUp(): void
    {
        $this->cache->get($this->data->user);
        $this->cache->get($this->data->admin);

        // Otherwise the assertFalse that follow would pass on an empty cache.
        self::assertTrue($this->isCached($this->data->user->getId()));
        self::assertTrue($this->isCached($this->data->admin->getId()));
    }

    private function isCached(?int $userId): bool
    {
        return self::getContainer()->get('dashboard.cache')->hasItem('dashboard_'.$userId);
    }
}
