<?php

namespace App\Controller;

use App\Repository\LoanRepository;
use App\Repository\PositionRepository;
use App\Service\DepositService;
use App\Service\FarmingService;
use App\Service\FiatExchangeRatesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CryptobookController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(FiatExchangeRatesService $fiatExchangeRatesService, PositionRepository $positionRepository, DepositService $depositService, LoanRepository $loanRepository, FarmingService $farmingService): Response
    {
        $positions = $positionRepository->getSumCoinByUser($this->getUser()) ?? [];
        $positions_stable = $positionRepository->getSumCoinByUser($this->getUser(), true) ?? [];
        $totalDepositUsd = $depositService->getTotalDepositUsdCurrentUser();

        $userFavoriteCurrency = $this->getUser()->getFavoriteFiatCurrency();
        $isFavoriteUsd = $userFavoriteCurrency->getFixerKey() === 'USD';

        $totalUsd = 0;
        foreach ($positions as $key => $value) {
            $valueUsd = $value['totalsum'] * $value['priceUsd'];
            $valueEur = $isFavoriteUsd ? $valueUsd : $fiatExchangeRatesService->toFavoriteCurrency($valueUsd);
            $value['valueUsd'] = $valueUsd;
            $value['valueEur'] = $valueEur;
            $positions[$key] = $value;
            $totalUsd += $valueUsd;
        }

        $totalUsdStable = 0;
        foreach ($positions_stable as $key => $value) {
            $valueUsd = $value['totalsum'] * $value['priceUsd'];
            $valueEur = $isFavoriteUsd ? $valueUsd : $fiatExchangeRatesService->toFavoriteCurrency($valueUsd);
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

        $totalYearFarmingUsd = $farmingService->getTotalFarmingByYearUsd();

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
