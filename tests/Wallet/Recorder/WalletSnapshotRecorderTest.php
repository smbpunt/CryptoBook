<?php

namespace App\Tests\Wallet\Recorder;

use App\Entity\Position;
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

    private EntityManagerInterface $em;
    private Dataset $data;
    private WalletSnapshotRecorder $recorder;
    private WalletSnapshotRepository $repository;

    protected function setUp(): void
    {
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->data = Dataset::load($this->em);
        $this->recorder = self::getContainer()->get(WalletSnapshotRecorder::class);
        $this->repository = self::getContainer()->get(WalletSnapshotRepository::class);
    }

    public function testRecordsOneSnapshotPerUserAndPerHour(): void
    {
        $clock = self::mockTime('2026-11-10 14:05:00 UTC');

        self::assertSame(1, $this->recorder->record());
        $clock->sleep(30 * 60);
        self::assertSame(0, $this->recorder->record());

        $snapshots = $this->repository->findBy(['owner' => $this->data->user]);
        self::assertCount(1, $snapshots);
        self::assertEquals(new \DateTimeImmutable('2026-11-10 14:00:00 UTC'), $snapshots[0]->getRecordedAt());
        self::assertSame(26000.0, $snapshots[0]->getValueUsd());

        $clock->sleep(30 * 60);
        self::assertSame(1, $this->recorder->record());
        self::assertCount(2, $this->repository->findBy(['owner' => $this->data->user]));
    }

    public function testRecordsZeroWhenEveryPositionIsClosed(): void
    {
        self::mockTime('2026-11-10 14:05:00 UTC');
        foreach ($this->em->getRepository(Position::class)->findBy(['owner' => $this->data->user]) as $position) {
            $position->setIsOpened(false);
        }
        $this->em->flush();

        $this->recorder->record();

        $snapshot = $this->repository->findOneBy(['owner' => $this->data->user]);
        self::assertInstanceOf(WalletSnapshot::class, $snapshot);
        self::assertSame(0.0, $snapshot->getValueUsd());
    }

    public function testSkipsUsersWithoutPosition(): void
    {
        self::mockTime('2026-11-10 14:05:00 UTC');

        $this->recorder->record();

        self::assertNull($this->repository->findOneBy(['owner' => $this->data->admin]));
    }
}
