<?php

namespace App\Dashboard\Builder;

use App\Dashboard\Dto\DashboardData;
use App\Dashboard\Dto\DashboardHolding;
use App\Deposit\Calculator\DepositTotalCalculator;
use App\Deposit\Repository\DepositRepository;
use App\FiatCurrency\Converter\FiatConverter;
use App\Loan\Repository\LoanRepository;
use App\Strategy\Farming\Builder\FarmingOverviewBuilder;
use App\User\Entity\User;
use App\User\Resolver\FavoriteCurrencyResolver;
use App\Wallet\Calculator\WalletValueCalculator;
use App\Wallet\Repository\WalletSnapshotRepository;
use Psr\Clock\ClockInterface;

/**
 * Computes the dashboard of a user, without cache.
 */
final readonly class DashboardBuilder
{
    public function __construct(
        private WalletValueCalculator $walletValueCalculator,
        private DepositRepository $depositRepository,
        private DepositTotalCalculator $depositTotalCalculator,
        private FarmingOverviewBuilder $farmingOverviewBuilder,
        private LoanRepository $loanRepository,
        private FiatConverter $fiatConverter,
        private FavoriteCurrencyResolver $favoriteCurrencyResolver,
        private WalletSnapshotRepository $snapshotRepository,
        private ClockInterface $clock,
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
            $this->farmingOverviewBuilder->build($user)->totalYearlyUsd(),
            (string) $currency->getFixerKey(),
            (string) $currency->getSymbol(),
            $this->fiatConverter->toUsd(1.0, $currency),
            $this->fiatConverter->fromUsd(1.0, $currency),
            $this->snapshotRepository->findStats($user, $this->clock->now()),
        );
    }
}
