<?php

namespace App\Wallet\Calculator;

use App\Entity\User;
use App\Repository\PositionRepository;
use App\Wallet\Dto\Holding;
use App\Wallet\Dto\WalletValue;

/**
 * Single source of truth for the value of a user's wallet: open positions valued at the current coin prices.
 */
final readonly class WalletValueCalculator
{
    public function __construct(private PositionRepository $positionRepository)
    {
    }

    public function calculate(User $user): WalletValue
    {
        return new WalletValue(
            $this->holdings($user, false),
            $this->holdings($user, true),
        );
    }

    /**
     * @return list<Holding>
     */
    private function holdings(User $user, bool $isStable): array
    {
        $rows = $this->positionRepository->getSumCoinByUser($user, $isStable);

        $values = array_map(static fn (array $row): float => (float) $row['totalsum'] * (float) $row['priceUsd'], $rows);
        $total = array_sum($values);

        $holdings = [];
        foreach ($rows as $i => $row) {
            $holdings[] = new Holding(
                symbol: (string) $row['symbol'],
                libelle: (string) $row['libelle'],
                color: $row['color'],
                urlImgThumb: $row['urlImgThumb'],
                quantity: (float) $row['totalsum'],
                priceUsd: (float) $row['priceUsd'],
                valueUsd: $values[$i],
                percent: $total > 0 ? round($values[$i] * 100 / $total, 2) : 0.0,
            );
        }

        usort($holdings, static fn (Holding $a, Holding $b): int => $b->valueUsd <=> $a->valueUsd);

        return $holdings;
    }
}
