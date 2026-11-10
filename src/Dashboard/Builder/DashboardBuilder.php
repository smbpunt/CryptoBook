<?php

namespace App\Dashboard\Builder;

use App\Dashboard\Dto\DashboardData;
use App\Dashboard\Dto\DashboardHolding;
use App\Entity\User;
use App\Repository\LoanRepository;
use App\Service\DepositService;
use App\Service\FarmingService;
use App\Service\FiatExchangeRatesService;
use App\Wallet\Calculator\WalletValueCalculator;

/**
 * Computes the dashboard of a user, without cache.
 */
final readonly class DashboardBuilder
{
    public function __construct(
        private WalletValueCalculator $walletValueCalculator,
        private DepositService $depositService,
        private FarmingService $farmingService,
        private LoanRepository $loanRepository,
        private FiatExchangeRatesService $fiatRates,
    ) {
    }

    public function build(User $user): DashboardData
    {
        $wallet = $this->walletValueCalculator->forUser($user);

        return new DashboardData(
            array_map(DashboardHolding::fromWalletHolding(...), $wallet->cryptoHoldings),
            array_map(DashboardHolding::fromWalletHolding(...), $wallet->stableHoldings),
            $wallet->cryptoUsd,
            $wallet->stableUsd,
            $this->depositService->getTotalDepositUsd($user),
            $this->loanRepository->getTotal($user),
            $this->farmingService->getTotalFarmingByYearUsd($user),
            (string) $this->fiatRates->getFavoriteCurrency($user)->getsymbol(),
            $this->fiatRates->getUsdRateFromFavoriteCurrency($user),
            $this->fiatRates->toFavoriteCurrency(1.0, $user),
        );
    }
}
