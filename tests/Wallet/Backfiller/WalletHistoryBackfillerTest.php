<?php

namespace App\Tests\Wallet\Backfiller;

use App\Coingecko\Exception\CoingeckoUnavailableException;
use App\Position\Entity\Position;
use App\Tests\Fixtures\Dataset;
use App\User\Entity\User;
use App\Wallet\Backfiller\WalletHistoryBackfiller;
use App\Wallet\Dto\BackfillReport;
use App\Wallet\Entity\WalletSnapshot;
use App\Wallet\Repository\WalletSnapshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Now is 2026-11-10 14:35 UTC: hours from 2026-10-11 14:00 to 2026-11-10 13:00 are filled.
 * user holds 0.5 BTC (50,000 $) and 1,000 USDC (stable), admin nothing.
 */
final class WalletHistoryBackfillerTest extends KernelTestCase
{
    use ClockSensitiveTrait;

    private const string START = '2026-10-11 14:00';
    private const string LAST = '2026-11-10 13:00';

    private EntityManagerInterface $em;
    private Dataset $data;
    private MockHttpClient $http;
    /** @var list<string> CoinGecko ids requested */
    private array $requested = [];

    protected function setUp(): void
    {
        self::mockTime('2026-11-10 14:35:00 UTC');
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->data = Dataset::load($this->em);
        $http = self::getContainer()->get('http_client.transport');
        \assert($http instanceof MockHttpClient);
        $this->http = $http;
    }

    public function testFillsTheLast30DaysWithTheHourlyPrices(): void
    {
        $this->pricesAre(['bitcoin' => [self::START => 40000, '2026-11-01 00:00' => 60000]]);

        self::assertEquals(new BackfillReport(users: 1, snapshots: 720, fetchedCoins: 1), $this->backfill());

        $snapshots = $this->snapshots($this->data->user);
        self::assertCount(720, $snapshots);
        // 0.5 BTC at 40,000 $ + 1,000 USDC at their current price.
        self::assertSnapshot(self::START, 21000.0, $snapshots[0]);
        self::assertSnapshot('2026-10-31 23:00', 21000.0, $snapshots[489]);
        self::assertSnapshot('2026-11-01 00:00', 31000.0, $snapshots[490]);
        // The current hour is left to wallet:snapshot.
        self::assertSnapshot(self::LAST, 31000.0, $snapshots[719]);
        self::assertSame(['bitcoin'], $this->requested);
        self::assertSame([], $this->snapshots($this->data->admin));
    }

    public function testFillsOnlyTheHoursBeforeTheFirstSnapshot(): void
    {
        $this->pricesAre(['bitcoin' => [self::START => 40000]]);
        $this->em->persist(new WalletSnapshot($this->data->user, self::utc('2026-11-05 10:00'), 123.0));
        $this->em->flush();

        self::assertSame(596, $this->backfill()->snapshots);

        $snapshots = $this->snapshots($this->data->user);
        self::assertCount(597, $snapshots);
        self::assertSnapshot('2026-11-05 09:00', 21000.0, $snapshots[595]);
        self::assertSnapshot('2026-11-05 10:00', 123.0, $snapshots[596]);

        // Run again: nothing left before the first snapshot, no call.
        $this->requested = [];
        self::assertSame(0, $this->backfill()->snapshots);
        self::assertSame([], $this->requested);
    }

    public function testDustKeepsItsCurrentPrice(): void
    {
        $this->pricesAre(['bitcoin' => [self::START => 40000]]);
        $this->open($this->data->admin, 0.001); // 2 $ of ETH.

        $this->backfill();

        self::assertNotContains('ethereum', $this->requested);
        $snapshots = $this->snapshots($this->data->admin);
        self::assertCount(720, $snapshots);
        self::assertSame([2.0], array_values(array_unique(array_map(static fn (WalletSnapshot $s): float => $s->getValueUsd(), $snapshots))));
    }

    public function testFetchedCoinCountsForEveryHolder(): void
    {
        $this->pricesAre(['bitcoin' => [self::START => 40000], 'ethereum' => [self::START => 3000]]);
        $this->open($this->data->user, 1);
        $this->open($this->data->admin, 0.001);

        self::assertSame(2, $this->backfill()->fetchedCoins);

        self::assertSame(['bitcoin', 'ethereum'], $this->requested);
        $snapshots = $this->snapshots($this->data->admin);
        self::assertSnapshot(self::START, 3.0, $snapshots[0]);
        self::assertSnapshot(self::LAST, 3.0, $snapshots[719]);
    }

    public function testCoinWithoutPricesKeepsItsCurrentPrice(): void
    {
        $this->pricesAre(['bitcoin' => []]);

        self::assertSame(1, $this->backfill()->fetchedCoins);

        $snapshots = $this->snapshots($this->data->user);
        self::assertCount(720, $snapshots);
        self::assertSnapshot(self::START, 26000.0, $snapshots[0]);
        self::assertSnapshot(self::LAST, 26000.0, $snapshots[719]);
    }

    public function testCoingeckoFailureWritesNothing(): void
    {
        $this->http->setResponseFactory(new MockResponse('', ['http_code' => 500]));

        try {
            $this->backfill();
            self::fail('The CoinGecko failure must be thrown.');
        } catch (CoingeckoUnavailableException) {
        }

        $this->em->clear();
        self::assertSame(0, self::getContainer()->get(WalletSnapshotRepository::class)->count());
    }

    public function testEmptyWalletIsSkipped(): void
    {
        $this->pricesAre([]);
        foreach ($this->em->getRepository(Position::class)->findBy(['owner' => $this->data->user]) as $position) {
            $position->setIsOpened(false);
        }
        $this->em->flush();

        self::assertEquals(new BackfillReport(0, 0, 0), $this->backfill());
        self::assertSame(0, $this->http->getRequestsCount());
    }

    private function backfill(): BackfillReport
    {
        return self::getContainer()->get(WalletHistoryBackfiller::class)->backfill();
    }

    /**
     * @param array<string, array<string, float>> $pricesByCoin price by UTC date, by CoinGecko id
     */
    private function pricesAre(array $pricesByCoin): void
    {
        $this->http->setResponseFactory(function (string $method, string $url) use ($pricesByCoin): MockResponse {
            self::assertSame(1, preg_match('#/coins/([^/]+)/market_chart\?#', $url, $matches));
            $id = $matches[1];
            $this->requested[] = $id;
            self::assertArrayHasKey($id, $pricesByCoin, \sprintf('Unexpected CoinGecko call for "%s".', $id));

            $prices = [];
            foreach ($pricesByCoin[$id] as $at => $price) {
                $prices[] = [self::utc($at)->getTimestamp() * 1000, $price];
            }

            return new JsonMockResponse(['prices' => $prices]);
        });
    }

    private function open(User $owner, float $eth): void
    {
        $this->em->persist(new Position($owner)->setCoin($this->data->eth)->setNbCoins($eth)->setEntryCost(1));
        $this->em->flush();
    }

    /**
     * @return list<WalletSnapshot> oldest first
     */
    private function snapshots(User $owner): array
    {
        $this->em->clear();

        return array_values(self::getContainer()->get(WalletSnapshotRepository::class)->findBy(['owner' => $owner->getId()], ['recordedAt' => 'ASC']));
    }

    private static function assertSnapshot(string $recordedAt, float $valueUsd, WalletSnapshot $snapshot): void
    {
        self::assertEquals(self::utc($recordedAt), $snapshot->getRecordedAt());
        self::assertSame($valueUsd, $snapshot->getValueUsd());
    }

    private static function utc(string $at): \DateTimeImmutable
    {
        return new \DateTimeImmutable($at.' UTC');
    }
}
