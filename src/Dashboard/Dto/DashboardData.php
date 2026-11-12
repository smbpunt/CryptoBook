<?php

namespace App\Dashboard\Dto;

use App\Wallet\Dto\WalletSnapshotStats;

/**
 * Everything the dashboard shows for a user. Scalars only: it is cached.
 */
final readonly class DashboardData
{
    public float $totalUsd;

    /** @var list<AllocationSlice> */
    public array $allocation;

    /**
     * @param list<DashboardHolding> $cryptoHoldings
     * @param list<DashboardHolding> $stableHoldings
     */
    public function __construct(
        public array $cryptoHoldings,
        public array $stableHoldings,
        public float $cryptoUsd,
        public float $stableUsd,
        public float $depositUsd,
        public float $loanUsd,
        public float $yearFarmingUsd,
        public string $currencySymbol,
        /** Value of one unit of the favorite currency, in USD. */
        public float $usdPerFavorite,
        /** Value of one USD, in the favorite currency. */
        public float $favoritePerUsd,
        public WalletSnapshotStats $snapshotStats = new WalletSnapshotStats(),
    ) {
        $this->totalUsd = $cryptoUsd + $stableUsd;
        $this->allocation = AllocationSlice::fromHoldings($cryptoHoldings);
    }

    public function toFavorite(float $amountUsd): float
    {
        return $amountUsd * $this->favoritePerUsd;
    }

    /**
     * Highest value ever: the snapshots or the current total, whichever is higher.
     */
    public function highUsd(): float
    {
        return max($this->snapshotStats->highUsd ?? 0.0, $this->totalUsd);
    }

    public function highAt(): ?\DateTimeImmutable
    {
        return $this->totalUsd > ($this->snapshotStats->highUsd ?? 0.0) ? null : $this->snapshotStats->highAt;
    }

    /**
     * Change since the snapshot of 24 hours ago, null without it.
     */
    public function dayChangeUsd(): ?float
    {
        return null === $this->snapshotStats->dayAgoUsd ? null : $this->totalUsd - $this->snapshotStats->dayAgoUsd;
    }

    public function dayChangePercent(): ?float
    {
        $dayAgoUsd = $this->snapshotStats->dayAgoUsd;

        return null !== $dayAgoUsd && $dayAgoUsd > 0 ? ($this->totalUsd - $dayAgoUsd) * 100 / $dayAgoUsd : null;
    }

    public function resultUsd(): float
    {
        return $this->totalUsd - $this->depositUsd;
    }

    public function resultPercent(): ?float
    {
        return $this->depositUsd > 0 ? $this->resultUsd() * 100 / $this->depositUsd : null;
    }

    /**
     * Share of the crypto in the total, in %.
     */
    public function cryptoPercent(): float
    {
        return $this->totalUsd > 0 ? $this->cryptoUsd * 100 / $this->totalUsd : 0.0;
    }
}
