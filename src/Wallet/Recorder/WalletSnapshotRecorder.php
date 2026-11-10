<?php

namespace App\Wallet\Recorder;

use App\Entity\User;
use App\Wallet\Calculator\WalletValueCalculator;
use App\Wallet\Entity\WalletSnapshot;
use App\Wallet\Repository\WalletSnapshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;

/**
 * Records one snapshot per user and per hour. Users already recorded for the current hour are skipped.
 */
final readonly class WalletSnapshotRecorder
{
    public function __construct(
        private WalletValueCalculator $calculator,
        private WalletSnapshotRepository $snapshotRepository,
        private EntityManagerInterface $em,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @return int number of snapshots recorded
     */
    public function record(): int
    {
        $now = $this->clock->now()->setTimezone(new \DateTimeZone('UTC'));
        $hour = $now->setTime((int) $now->format('G'), 0);
        $alreadyRecorded = array_flip($this->snapshotRepository->findOwnerIdsRecordedAt($hour));

        $recorded = 0;
        foreach ($this->calculator->forAllUsers() as $ownerId => $valuation) {
            if (isset($alreadyRecorded[$ownerId])) {
                continue;
            }

            $this->em->persist(new WalletSnapshot($this->em->getReference(User::class, $ownerId), $hour, $valuation->totalUsd));
            ++$recorded;
        }
        $this->em->flush();

        return $recorded;
    }
}
