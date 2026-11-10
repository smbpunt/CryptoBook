<?php

namespace App\Wallet\Calculator;

use App\Entity\Cryptocurrency;
use App\Entity\User;
use App\Repository\PositionRepository;
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
            $rowsByOwner[$row['ownerId']][] = $row;
        }

        return array_map($this->valuate(...), $rowsByOwner);
    }

    /**
     * @param list<array{0: Cryptocurrency, ownerId: int, quantity: float}> $rows
     */
    private function valuate(array $rows): WalletValuation
    {
        $crypto = [];
        $stable = [];
        foreach ($rows as $row) {
            if ($row['quantity'] <= 0.0) {
                continue;
            }

            $coin = $row[0];
            $priceUsd = $coin->getPriceUsd() ?? 0.0;
            $line = ['coin' => $coin, 'quantity' => $row['quantity'], 'priceUsd' => $priceUsd, 'valueUsd' => $row['quantity'] * $priceUsd];
            if ($coin->isIsStable()) {
                $stable[] = $line;
            } else {
                $crypto[] = $line;
            }
        }

        [$cryptoHoldings, $cryptoUsd] = $this->holdings($crypto);
        [$stableHoldings, $stableUsd] = $this->holdings($stable);

        return new WalletValuation($cryptoHoldings, $stableHoldings, $cryptoUsd, $stableUsd);
    }

    /**
     * @param list<array{coin: Cryptocurrency, quantity: float, priceUsd: float, valueUsd: float}> $lines
     *
     * @return array{list<WalletHolding>, float}
     */
    private function holdings(array $lines): array
    {
        $totalUsd = array_sum(array_column($lines, 'valueUsd'));
        usort($lines, static fn (array $a, array $b): int => $b['valueUsd'] <=> $a['valueUsd']);

        $holdings = array_map(static fn (array $line): WalletHolding => new WalletHolding(
            $line['coin'],
            $line['quantity'],
            $line['priceUsd'],
            $line['valueUsd'],
            $totalUsd > 0 ? round($line['valueUsd'] * 100 / $totalUsd, 2) : 0.0,
        ), $lines);

        return [$holdings, $totalUsd];
    }
}
