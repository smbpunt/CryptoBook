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
        $rows = $this->positionRepository->getHoldingsByUser($user);

        return new WalletValue(
            $this->holdings(array_values(array_filter($rows, static fn (array $row): bool => !$row['isStable']))),
            $this->holdings(array_values(array_filter($rows, static fn (array $row): bool => $row['isStable']))),
        );
    }

    /**
     * @param list<array{totalsum: float|string, symbol: string, libelle: string, color: ?string, priceUsd: float, urlImgThumb: ?string, isStable: bool}> $rows
     *
     * @return list<Holding>
     */
    private function holdings(array $rows): array
    {
        $values = array_map(static fn (array $row): float => (float) $row['totalsum'] * $row['priceUsd'], $rows);
        $total = array_sum($values);

        $holdings = [];
        foreach ($rows as $i => $row) {
            $holdings[] = new Holding(
                symbol: $row['symbol'],
                libelle: $row['libelle'],
                color: $row['color'],
                urlImgThumb: $row['urlImgThumb'],
                quantity: (float) $row['totalsum'],
                priceUsd: $row['priceUsd'],
                valueUsd: $values[$i],
                percent: $total > 0 ? round($values[$i] * 100 / $total, 2) : 0.0,
            );
        }

        usort($holdings, static fn (Holding $a, Holding $b): int => $b->valueUsd <=> $a->valueUsd);

        return $holdings;
    }
}
