<?php

namespace App\Tests\Dashboard\Cache;

use App\Dashboard\Cache\DashboardCache;
use App\Deposit\Entity\Deposit;
use App\Loan\Entity\Loan;
use App\Position\Entity\Position;
use App\Position\Entity\Vente;
use App\Strategy\Farming\Entity\StrategyFarming;
use App\Strategy\Lp\Entity\StrategyLp;
use App\Tests\Fixtures\Dataset;
use App\Wallet\Entity\WalletSnapshot;
use App\Wallet\Enum\HistoryPeriod;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * Adding, changing or deleting each kind of data the dashboard reads.
     *
     * @return iterable<string, array{\Closure(Dataset, EntityManagerInterface): mixed}>
     */
    public static function ownDataChanges(): iterable
    {
        yield 'position added' => [static fn (Dataset $d, EntityManagerInterface $em) => $em->persist(new Position($d->user)->setCoin($d->eth)->setNbCoins(1)->setEntryCost(2000))];
        yield 'position changed' => [static fn (Dataset $d) => $d->position->setNbCoins(1)];
        yield 'position deleted' => [static fn (Dataset $d, EntityManagerInterface $em) => $em->remove($d->position)];
        yield 'deposit added' => [static fn (Dataset $d, EntityManagerInterface $em) => $em->persist(new Deposit($d->user)->setAmount(500)
            ->setExchange($d->exchange)->setType($d->depositType)->setFiatCurrency($d->eur)->setDepositedAt(new \DateTimeImmutable('2024-06-01')))];
        yield 'deposit changed' => [static fn (Dataset $d) => $d->deposit->setAmount(2000)];
        yield 'deposit deleted' => [static fn (Dataset $d, EntityManagerInterface $em) => $em->remove($d->deposit)];
        yield 'loan added' => [static fn (Dataset $d, EntityManagerInterface $em) => $em->persist(new Loan($d->user)->setCoin($d->usdc)->setNbCoins(100)->setDapp($d->aave)->setDescription(''))];
        yield 'loan changed' => [static fn (Dataset $d) => $d->loan->setNbCoins(800)];
        yield 'loan deleted' => [static fn (Dataset $d, EntityManagerInterface $em) => $em->remove($d->loan)];
        yield 'farming added' => [static fn (Dataset $d, EntityManagerInterface $em) => $em->persist(new StrategyFarming($d->user)->setCoin($d->btc)->setNbCoins(0.1)->setApr(3)->setDapp($d->aave)->setDescription(''))];
        yield 'farming changed' => [static fn (Dataset $d) => $d->farming->setApr(8)];
        yield 'farming deleted' => [static fn (Dataset $d, EntityManagerInterface $em) => $em->remove($d->farming)];
        yield 'LP added' => [static fn (Dataset $d, EntityManagerInterface $em) => $em->persist(new StrategyLp($d->user)->setCoin1($d->eth)->setCoin2($d->usdc)->setDapp($d->aave)
            ->setNbCoin1(1)->setNbCoin2(2000)->setPriceCoin1(2000)->setPriceCoin2(1)->setApr(4)->setDescription(''))];
        yield 'LP changed' => [static fn (Dataset $d) => $d->lp->setApr(20)];
        yield 'LP deleted' => [static fn (Dataset $d, EntityManagerInterface $em) => $em->remove($d->lp)];
    }

    /**
     * @param \Closure(Dataset, EntityManagerInterface): mixed $change
     */
    #[DataProvider('ownDataChanges')]
    public function testOwnDataChangeInvalidatesOnlyItsOwner(\Closure $change): void
    {
        $this->warmUp();
        $this->cache->getHistory($this->data->user, HistoryPeriod::Month);
        self::assertTrue($this->isHistoryCached($this->data->user->getId(), HistoryPeriod::Month));

        $change($this->data, $this->em);
        $this->em->flush();

        self::assertFalse($this->isCached($this->data->user->getId()));
        self::assertFalse($this->isHistoryCached($this->data->user->getId(), HistoryPeriod::Month));
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

    public function testSnapshotInvalidatesOnlyItsOwner(): void
    {
        $this->warmUp();
        $this->cache->getHistory($this->data->user, HistoryPeriod::Month);
        self::assertTrue($this->isHistoryCached($this->data->user->getId(), HistoryPeriod::Month));

        $this->em->persist(new WalletSnapshot($this->data->user, new \DateTimeImmutable('2024-01-01 10:00'), 26000));
        $this->em->flush();

        self::assertFalse($this->isCached($this->data->user->getId()));
        self::assertFalse($this->isHistoryCached($this->data->user->getId(), HistoryPeriod::Month));
        self::assertTrue($this->isCached($this->data->admin->getId()));
    }

    public function testDataTheDashboardDoesNotReadKeepsTheCache(): void
    {
        $this->warmUp();

        $this->data->nft->setPriceUsd(3000);
        $this->data->project->setNote('Other note');
        $this->data->dca->setFiatToDcaEur(200);
        $this->data->user->setLastConnectedAt(new \DateTimeImmutable());
        $this->em->flush();

        self::assertTrue($this->isCached($this->data->user->getId()));
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
        return self::getContainer()->get('dashboard.cache')->hasItem(DashboardCache::key((int) $userId));
    }

    private function isHistoryCached(?int $userId, HistoryPeriod $period): bool
    {
        return self::getContainer()->get('dashboard.cache')->hasItem(DashboardCache::historyKey((int) $userId, $period));
    }
}
