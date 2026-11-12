<?php

namespace App\Wallet\Calculator;

use App\Position\Dto\CoinQuantity;
use App\Position\Repository\PositionRepository;
use App\User\Entity\User;
use App\Wallet\Dto\WalletHolding;
use App\Wallet\Dto\WalletValuation;

/**
 * Values wallets from the open positions and the current coin prices. Single source for the dashboard and the snapshots.
 */
final readonly class WalletValueCalculator
{
    public function __construct(private PositionRepository $positionRepository)
    {
    }

    public function forUser(User $user): WalletValuation
    {
        return $this->valuate($this->positionRepository->sumQuantitiesByOwnerAndCoin($user));
    }

    /**
     * Every owner with at least one position, open or not.
     *
     * @return array<int, WalletValuation> keyed by owner id
     */
    public function forAllUsers(): array
    {
        $rowsByOwner = [];
        foreach ($this->positionRepository->sumQuantitiesByOwnerAndCoin() as $row) {
            $rowsByOwner[$row->ownerId][] = $row;
        }

        return array_map($this->valuate(...), $rowsByOwner);
    }

    /**
     * @param list<CoinQuantity> $rows
     */
    private function valuate(array $rows): WalletValuation
    {
        $crypto = [];
        $stable = [];
        foreach ($rows as $row) {
            if ($row->quantity <= 0.0) {
                continue;
            }

            if ($row->coin->isIsStable()) {
                $stable[] = $row;
            } else {
                $crypto[] = $row;
            }
        }

        [$cryptoHoldings, $cryptoUsd] = $this->holdings($crypto);
        [$stableHoldings, $stableUsd] = $this->holdings($stable);

        return new WalletValuation($cryptoHoldings, $stableHoldings, $cryptoUsd, $stableUsd);
    }

    /**
     * Two passes: the total of the group first, then each holding with its share of it.
     *
     * @param list<CoinQuantity> $rows
     *
     * @return array{list<WalletHolding>, float}
     */
    private function holdings(array $rows): array
    {
        $totalUsd = 0.0;
        foreach ($rows as $row) {
            $totalUsd += $row->quantity * ($row->coin->getPriceUsd() ?? 0.0);
        }

        $holdings = array_map(static function (CoinQuantity $row) use ($totalUsd): WalletHolding {
            $priceUsd = $row->coin->getPriceUsd() ?? 0.0;
            $valueUsd = $row->quantity * $priceUsd;

            return new WalletHolding(
                $row->coin,
                $row->quantity,
                $priceUsd,
                $valueUsd,
                $totalUsd > 0 ? round($valueUsd * 100 / $totalUsd, 2) : 0.0,
            );
        }, $rows);
        usort($holdings, static fn (WalletHolding $a, WalletHolding $b): int => $b->valueUsd <=> $a->valueUsd);

        return [$holdings, $totalUsd];
    }
}
