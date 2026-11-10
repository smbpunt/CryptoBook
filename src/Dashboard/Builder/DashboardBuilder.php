<?php

namespace App\Dashboard\Builder;

use App\Dashboard\Dto\DashboardData;
use App\Dashboard\Dto\DashboardHolding;
use App\Deposit\Calculator\DepositTotalCalculator;
use App\Deposit\Repository\DepositRepository;
use App\Entity\User;
use App\Loan\Repository\LoanRepository;
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
        private DepositRepository $depositRepository,
        private DepositTotalCalculator $depositTotalCalculator,
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
            $this->depositTotalCalculator->totalUsd($this->depositRepository->findByOwnerWithCurrency($user)),
            $this->loanRepository->getTotal($user),
            $this->farmingService->getTotalFarmingByYearUsd($user),
            (string) $this->fiatRates->getFavoriteCurrency($user)->getsymbol(),
            $this->fiatRates->getUsdRateFromFavoriteCurrency($user),
            $this->fiatRates->toFavoriteCurrency(1.0, $user),
        );
    }
}
