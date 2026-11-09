<?php

namespace App\Tests\Wallet\Recorder;

use App\Tests\Fixtures\Dataset;
use App\Wallet\Entity\WalletSnapshot;
use App\Wallet\Recorder\WalletSnapshotRecorder;
use App\Wallet\Repository\WalletSnapshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

final class WalletSnapshotRecorderTest extends KernelTestCase
{
    use ClockSensitiveTrait;

    public function testRecordsTheWalletValueOfEveryUserAtTheStartOfTheHour(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $d = Dataset::load($em);
        self::mockTime('2026-03-01 10:42:17');

        $count = self::getContainer()->get(WalletSnapshotRecorder::class)->record();

        self::assertSame(2, $count);
        $snapshots = $this->snapshotsByOwner();
        self::assertCount(2, $snapshots);
        // 0.5 BTC at 50 000 $ + 1 000 USDC at 1 $.
        self::assertSame(26000.0, $snapshots[$d->user->getId()]->getValueUsd());
        self::assertSame(0.0, $snapshots[$d->admin->getId()]->getValueUsd());
        self::assertEquals(new \DateTimeImmutable('2026-03-01 10:00:00'), $snapshots[$d->user->getId()]->getTakenAt());
    }

    public function testRunningTwiceInTheSameHourUpdatesTheSnapshot(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $d = Dataset::load($em);
        $recorder = self::getContainer()->get(WalletSnapshotRecorder::class);

        self::mockTime('2026-03-01 10:05:00');
        $recorder->record();
        $d->btc->setPriceUsd(60000);
        $em->flush();
        self::mockTime('2026-03-01 10:55:00');
        $recorder->record();
        self::mockTime('2026-03-01 11:05:00');
        $recorder->record();

        $em->clear();
        $values = [];
        foreach (self::getContainer()->get(WalletSnapshotRepository::class)->findBy(['owner' => $d->user->getId()]) as $snapshot) {
            $values[$snapshot->getTakenAt()->format('H:i')] = $snapshot->getValueUsd();
        }
        ksort($values);
        self::assertSame(['10:00' => 31000.0, '11:00' => 31000.0], $values);
    }

    public function testHoursAreTruncatedInUtcSoDaylightSavingTimeKeepsEveryHour(): void
    {
        $d = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $recorder = self::getContainer()->get(WalletSnapshotRecorder::class);

        // Europe/Paris back to winter time: 02:05 happens twice.
        self::mockTime(new \DateTimeImmutable('2026-10-25 02:05:00+02:00'));
        $recorder->record();
        self::mockTime(new \DateTimeImmutable('2026-10-25 02:05:00+01:00'));
        $recorder->record();

        $takenAt = array_map(
            static fn (WalletSnapshot $s): string => $s->getTakenAt()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i'),
            self::getContainer()->get(WalletSnapshotRepository::class)->findBy(['owner' => $d->user->getId()]),
        );
        sort($takenAt);
        self::assertSame(['2026-10-25 00:00', '2026-10-25 01:00'], $takenAt);
    }

    /**
     * @return array<int, WalletSnapshot>
     */
    private function snapshotsByOwner(): array
    {
        $snapshots = [];
        foreach (self::getContainer()->get(WalletSnapshotRepository::class)->findAll() as $snapshot) {
            $snapshots[(int) $snapshot->getOwner()->getId()] = $snapshot;
        }

        return $snapshots;
    }
}
