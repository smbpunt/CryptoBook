<?php

namespace App\Tests\Wallet\Recorder;

use App\Tests\Fixtures\Dataset;
use App\Wallet\Calculator\WalletValueCalculator;
use App\Wallet\Entity\WalletSnapshot;
use App\Wallet\Recorder\WalletSnapshotRecorder;
use App\Wallet\Repository\WalletSnapshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

final class WalletSnapshotRecorderTest extends KernelTestCase
{
    use ClockSensitiveTrait;

    private EntityManagerInterface $em;
    private Dataset $data;
    private WalletSnapshotRecorder $recorder;

    protected function setUp(): void
    {
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->data = Dataset::load($this->em);
        $this->recorder = self::getContainer()->get(WalletSnapshotRecorder::class);
    }

    public function testRecordsTheCalculatedValueOfEveryUser(): void
    {
        $calculator = self::getContainer()->get(WalletValueCalculator::class);
        $expected = [
            $this->data->user->getId() => $calculator->calculate($this->data->user)->totalUsd(),
            $this->data->admin->getId() => $calculator->calculate($this->data->admin)->totalUsd(),
        ];

        self::assertSame(2, $this->recorder->record());

        $values = [];
        foreach ($this->reloadSnapshots(allUsers: true) as $snapshot) {
            $values[$snapshot->getOwner()->getId()] = $snapshot->getValueUsd();
        }
        self::assertEquals($expected, $values);
    }

    public function testRunningTwiceInTheSameHourUpdatesTheSnapshot(): void
    {
        self::mockTime('2026-03-01 10:05:00 UTC');
        $this->recorder->record();
        $before = $this->valueOf($this->reloadSnapshots());

        $this->em->find($this->data->btc::class, $this->data->btc->getId())?->setPriceUsd(60000);
        $this->em->flush();
        self::mockTime('2026-03-01 10:55:00 UTC');
        $this->recorder->record();

        $snapshots = $this->reloadSnapshots();
        self::assertCount(1, $snapshots);
        self::assertGreaterThan($before, $this->valueOf($snapshots));
    }

    public function testHoursAreUtcHoursWhateverTheServerTimezone(): void
    {
        // Kolkata is UTC+05:30: a local hour would start at half past in UTC.
        self::mockTime(new \DateTimeImmutable('2026-03-01 10:42', new \DateTimeZone('Asia/Kolkata')));
        $this->recorder->record();

        $takenAt = $this->reloadSnapshots()[0]->getTakenAt()->setTimezone(new \DateTimeZone('UTC'));
        self::assertSame('2026-03-01 05:00', $takenAt->format('Y-m-d H:i'));
    }

    public function testDaylightSavingTimeChangeKeepsEveryHour(): void
    {
        // Europe/Paris back to winter time: local 02:05 happens twice, at 00:05 and 01:05 UTC.
        $paris = new \DateTimeZone('Europe/Paris');
        self::mockTime(new \DateTimeImmutable('2026-10-25 00:05 UTC')->setTimezone($paris));
        $this->recorder->record();
        self::mockTime(new \DateTimeImmutable('2026-10-25 01:05 UTC')->setTimezone($paris));
        $this->recorder->record();

        $takenAt = array_map(
            static fn (WalletSnapshot $s): string => $s->getTakenAt()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i'),
            $this->reloadSnapshots(),
        );
        sort($takenAt);
        self::assertSame(['2026-10-25 00:00', '2026-10-25 01:00'], $takenAt);
    }

    /**
     * Snapshots read back from the database: the dataset user's, or everyone's.
     *
     * @return list<WalletSnapshot>
     */
    private function reloadSnapshots(bool $allUsers = false): array
    {
        $this->em->clear();
        $repository = self::getContainer()->get(WalletSnapshotRepository::class);

        return array_values($allUsers ? $repository->findAll() : $repository->findBy(['owner' => $this->data->user->getId()]));
    }

    /**
     * @param list<WalletSnapshot> $snapshots
     */
    private function valueOf(array $snapshots): float
    {
        self::assertCount(1, $snapshots);

        return $snapshots[0]->getValueUsd();
    }
}
