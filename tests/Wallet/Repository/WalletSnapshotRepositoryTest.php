<?php

namespace App\Tests\Wallet\Repository;

use App\Tests\Fixtures\Dataset;
use App\User\Entity\User;
use App\Wallet\Dto\SnapshotPoint;
use App\Wallet\Entity\WalletSnapshot;
use App\Wallet\Enum\HistoryPeriod;
use App\Wallet\Repository\WalletSnapshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class WalletSnapshotRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private Dataset $data;
    private WalletSnapshotRepository $repository;
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->data = Dataset::load($this->em);
        $this->repository = self::getContainer()->get(WalletSnapshotRepository::class);
        $this->now = new \DateTimeImmutable('2026-11-10 14:20 UTC');
    }

    public function testHourlyPeriodReturnsEverySnapshotOfThePeriod(): void
    {
        $this->snapshots($this->data->user, [
            '2026-11-09 13:00' => 1.0, // Before the 24 hours.
            '2026-11-09 15:00' => 2.0,
            '2026-11-10 14:00' => 4.0,
            '2026-11-10 09:00' => 3.0,
        ]);
        $this->snapshots($this->data->admin, ['2026-11-10 10:00' => 99.0]);

        self::assertEquals([
            new SnapshotPoint(new \DateTimeImmutable('2026-11-09 15:00 UTC'), 2.0),
            new SnapshotPoint(new \DateTimeImmutable('2026-11-10 09:00 UTC'), 3.0),
            new SnapshotPoint(new \DateTimeImmutable('2026-11-10 14:00 UTC'), 4.0),
        ], $this->points(HistoryPeriod::Day));
    }

    public function testDailyPeriodReturnsTheLastSnapshotOfEachUtcDay(): void
    {
        $this->snapshots($this->data->user, [
            '2026-10-01 10:00' => 1.0, // Before the 30 days.
            '2026-11-01 10:00' => 2.0,
            '2026-11-01 23:00' => 3.0,
            '2026-11-02 00:00' => 4.0,
            '2026-11-10 14:00' => 5.0,
        ]);
        $this->snapshots($this->data->admin, ['2026-11-01 23:30' => 99.0]);

        self::assertSame([
            ['2026-11-01 23:00', 3.0],
            ['2026-11-02 00:00', 4.0],
            ['2026-11-10 14:00', 5.0],
        ], $this->points(HistoryPeriod::Month, true));
        // No start: the snapshot of October too.
        self::assertSame([
            ['2026-10-01 10:00', 1.0],
            ['2026-11-01 23:00', 3.0],
            ['2026-11-02 00:00', 4.0],
            ['2026-11-10 14:00', 5.0],
        ], $this->points(HistoryPeriod::All, true));
    }

    public function testStats(): void
    {
        $this->snapshots($this->data->user, [
            '2026-10-01 10:00' => 500.0,
            '2026-11-08 10:00' => 100.0, // More than 48 hours ago.
            '2026-11-09 12:00' => 200.0,
            '2026-11-09 14:00' => 300.0, // Last one at least 24 hours ago.
            '2026-11-10 14:00' => 400.0,
        ]);
        $this->snapshots($this->data->admin, ['2026-11-10 10:00' => 9999.0]);

        $stats = $this->repository->findStats($this->data->user, $this->now);

        self::assertSame(500.0, $stats->highUsd);
        self::assertEquals(new \DateTimeImmutable('2026-10-01 10:00 UTC'), $stats->highAt);
        self::assertSame(300.0, $stats->dayAgoUsd);
        self::assertEquals(new \DateTimeImmutable('2026-11-10 14:00 UTC'), $stats->lastRecordedAt);
    }

    public function testStatsWithoutSnapshots(): void
    {
        $this->snapshots($this->data->user, ['2026-11-10 10:00' => 1.0]);

        $stats = $this->repository->findStats($this->data->admin, $this->now);

        self::assertNull($stats->highUsd);
        self::assertNull($stats->lastRecordedAt);
        self::assertNull($this->repository->findStats($this->data->user, $this->now)->dayAgoUsd);
    }

    /**
     * @param array<string, float> $values by UTC date
     */
    private function snapshots(User $owner, array $values): void
    {
        foreach ($values as $at => $valueUsd) {
            $this->em->persist(new WalletSnapshot($owner, new \DateTimeImmutable($at.' UTC'), $valueUsd));
        }
        $this->em->flush();
    }

    /**
     * @return list<SnapshotPoint>|list<array{string, float}>
     */
    private function points(HistoryPeriod $period, bool $asArray = false): array
    {
        $points = $this->repository->findPoints($this->data->user, $period, $this->now);

        return $asArray
            ? array_map(static fn (SnapshotPoint $point): array => [$point->recordedAt->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i'), $point->valueUsd], $points)
            : $points;
    }
}
