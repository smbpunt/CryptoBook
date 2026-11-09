<?php

namespace App\Wallet\Recorder;

use App\Repository\UserRepository;
use App\Wallet\Calculator\WalletValueCalculator;
use App\Wallet\Entity\WalletSnapshot;
use App\Wallet\Repository\WalletSnapshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;

/**
 * Records the current wallet value of every user, once per hour.
 */
final readonly class WalletSnapshotRecorder
{
    public function __construct(
        private UserRepository $userRepository,
        private WalletSnapshotRepository $snapshotRepository,
        private WalletValueCalculator $walletValueCalculator,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
    ) {
    }

    /**
     * Running it again within the same hour updates that hour's snapshots.
     *
     * @return int number of users recorded
     */
    public function record(): int
    {
        $now = $this->clock->now();
        $takenAt = $now->setTime((int) $now->format('G'), 0);
        $existing = $this->snapshotRepository->findByTakenAtIndexedByOwner($takenAt);

        $count = 0;
        foreach ($this->userRepository->findAll() as $user) {
            $valueUsd = $this->walletValueCalculator->calculate($user)->totalUsd();

            $snapshot = $existing[$user->getId()] ?? null;
            if (null === $snapshot) {
                $this->entityManager->persist(new WalletSnapshot($user, $takenAt, $valueUsd));
            } else {
                $snapshot->setValueUsd($valueUsd);
            }
            ++$count;
        }
        $this->entityManager->flush();

        return $count;
    }
}
