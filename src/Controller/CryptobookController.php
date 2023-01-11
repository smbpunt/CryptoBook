<?php

namespace App\Controller;

use App\Entity\FiatCurrency;
use App\Repository\DepositRepository;
use App\Repository\LoanRepository;
use App\Repository\PositionRepository;
use App\Repository\StrategyFarmingRepository;
use App\Repository\StrategyLpRepository;
use App\Service\FiatExchangeRatesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CryptobookController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(FiatExchangeRatesService $fiatExchangeRatesService,PositionRepository $positionRepository, DepositRepository $depositRepository, StrategyFarmingRepository $strategyFarmingRepository, StrategyLpRepository $strategyLpRepository, LoanRepository $loanRepository): Response
    {
        $positions = $positionRepository->getSumCoinByUser($this->getUser()) ?? [];
        $positions_stable = $positionRepository->getSumCoinByUser($this->getUser(), true) ?? [];
        $deposits = $depositRepository->findBy(['owner' => $this->getUser()]);

        $totalDepositUsd = 0;
        foreach ($deposits as $deposit) {
            $totalDepositUsd += $deposit->getAmountUsd();
        }

        $totalUsd = 0;
        foreach ($positions as $key => $value) {
            $valueUsd = $value['totalsum'] * $value['priceUsd'];
            $valueEur = $fiatExchangeRatesService->toFavoriteCurrency($valueUsd);
            $value['valueUsd'] = $valueUsd;
            $value['valueEur'] = $valueEur;
            $positions[$key] = $value;
            $totalUsd += $valueUsd;
        }

        $totalUsdStable = 0;
        foreach ($positions_stable as $key => $value) {
            $valueUsd = $value['totalsum'] * $value['priceUsd'];
            $valueEur = $fiatExchangeRatesService->toFavoriteCurrency($valueUsd);
            $value['valueUsd'] = $valueUsd;
            $value['valueEur'] = $valueEur;
            $positions_stable[$key] = $value;
            $totalUsdStable += $valueUsd;
        }

        foreach ($positions as $key => $value) {
            $value['percent'] = round($value['valueUsd'] * 100 / $totalUsd, 2);
            $positions[$key] = $value;
        }

        foreach ($positions_stable as $key => $value) {
            $value['percent'] = round($value['valueUsd'] * 100 / $totalUsdStable, 2);
            $positions_stable[$key] = $value;
        }

        array_multisort(array_column($positions, 'valueUsd'), SORT_DESC, $positions);

        $strategies = $strategyFarmingRepository->findBy([
            'owner' => $this->getUser()
        ]);
        $strategies_lp = $strategyLpRepository->findBy([
            'owner' => $this->getUser()
        ]);

        $totalYearFarmingUsd = 0;
        foreach ($strategies as $strategy) {
            $value_dollar = $strategy->getCoin()->getPriceUsd() * $strategy->getNbCoins();
            $totalYearFarmingUsd += $strategy->getApr() * $value_dollar / 100;
        }

        foreach ($strategies_lp as $strategy) {
            $value_dollar = $strategy->getCoin1()->getPriceUsd() * $strategy->getNbCoin1() + $strategy->getCoin2()->getPriceUsd() * $strategy->getNbCoin2();
            $totalYearFarmingUsd += $strategy->getApr() * $value_dollar / 100;
        }

        $totalLoan = $loanRepository->getTotal($this->getUser());

        return $this->render('cryptobook/index.html.twig', [
            'positions' => $positions,
            'positions_stable' => $positions_stable,
            'totalDepositUsd' => $totalDepositUsd,
            'totalUsd' => $totalUsd,
            'totalStableUsd' => $totalUsdStable,
            'totalYearFarmingUsd' => $totalYearFarmingUsd,
            'totalLoanUsd' => $totalLoan,
        ]);
    }
}
