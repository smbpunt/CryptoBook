<?php

namespace App\Dashboard\Builder;

use App\Dashboard\Dto\WalletHistory;
use App\Dashboard\Dto\WalletHistoryPoint;
use App\Deposit\Calculator\DepositTimelineCalculator;
use App\Deposit\Repository\DepositRepository;
use App\User\Entity\User;
use App\Wallet\Enum\HistoryPeriod;
use App\Wallet\Repository\WalletSnapshotRepository;
use Psr\Clock\ClockInterface;

/**
 * Wallet history of a period: the snapshots, then the current total so the curve ends on the amount displayed.
 */
final readonly class WalletHistoryBuilder
{
    public function __construct(
        private WalletSnapshotRepository $snapshotRepository,
        private DepositRepository $depositRepository,
        private DepositTimelineCalculator $depositTimelineCalculator,
        private ClockInterface $clock,
    ) {
    }

    public function build(User $user, HistoryPeriod $period, float $currentTotalUsd): WalletHistory
    {
        $now = $this->clock->now();
        $snapshots = $this->snapshotRepository->findPoints($user, $period, $now);
        if ([] === $snapshots) {
            return new WalletHistory($period);
        }

        $steps = $this->depositTimelineCalculator->steps($this->depositRepository->findByOwnerWithCurrency($user));
        $points = [];
        foreach ($snapshots as $snapshot) {
            $points[] = new WalletHistoryPoint($snapshot->recordedAt, $snapshot->valueUsd, $this->depositTimelineCalculator->totalAt($steps, $snapshot->recordedAt));
        }
        $points[] = new WalletHistoryPoint($now, $currentTotalUsd, $this->depositTimelineCalculator->totalAt($steps, $now));

        $firstUsd = $points[0]->valueUsd;
        $changeUsd = $currentTotalUsd - $firstUsd;
        $since = $period->since($now);

        return new WalletHistory(
            $period,
            $points,
            $changeUsd,
            $firstUsd > 0 ? $changeUsd * 100 / $firstUsd : null,
            // The first point is the first hourly snapshot, or the last one of the first day.
            null === $since || $points[0]->at < $since->modify($period->isHourly() ? '+1 hour' : '+1 day'),
        );
    }
}
