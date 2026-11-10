<?php

namespace App\Dashboard\Builder;

use App\Dashboard\Dto\DashboardData;
use App\Dashboard\Dto\DashboardHolding;
use App\Deposit\Calculator\DepositTotalCalculator;
use App\Deposit\Repository\DepositRepository;
use App\Entity\User;
use App\FiatCurrency\Converter\FiatConverter;
use App\Loan\Repository\LoanRepository;
use App\Service\FarmingService;
use App\User\Resolver\FavoriteCurrencyResolver;
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
        private FiatConverter $fiatConverter,
        private FavoriteCurrencyResolver $favoriteCurrencyResolver,
    ) {
    }

    public function build(User $user): DashboardData
    {
        $wallet = $this->walletValueCalculator->forUser($user);
        $currency = $this->favoriteCurrencyResolver->resolve($user);

        return new DashboardData(
            array_map(DashboardHolding::fromWalletHolding(...), $wallet->cryptoHoldings),
            array_map(DashboardHolding::fromWalletHolding(...), $wallet->stableHoldings),
            $wallet->cryptoUsd,
            $wallet->stableUsd,
            $this->depositTotalCalculator->totalUsd($this->depositRepository->findByOwnerWithCurrency($user)),
            $this->loanRepository->sumValueUsd($user),
            $this->farmingService->getTotalFarmingByYearUsd($user),
            (string) $currency->getSymbol(),
            $this->fiatConverter->toUsd(1.0, $currency),
            $this->fiatConverter->fromUsd(1.0, $currency),
        );
    }
}
