<?php

namespace App\Controller;

use App\Repository\CryptocurrencyRepository;
use App\Repository\DepositRepository;
use App\Repository\LoanRepository;
use App\Repository\PositionRepository;
use App\Repository\StrategyFarmingRepository;
use App\Repository\StrategyLpRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CryptobookController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(PositionRepository $positionRepository, DepositRepository $depositRepository, StrategyFarmingRepository $strategyFarmingRepository, StrategyLpRepository $strategyLpRepository, CryptocurrencyRepository $cryptocurrencyRepository, LoanRepository $loanRepository): Response
    {
        $positions = $positionRepository->getSumCoinByUser($this->getUser()) ?? [];
        $positions_stable = $positionRepository->getSumCoinByUser($this->getUser(), true) ?? [];
        $totalDepositEur = $depositRepository->getTotal($this->getUser()) ?? 0.0;
        $totalUsd = 0;
        $totalEur = 0;
        $totalUsdStable = 0;
        $totalEurStable = 0;

        $jeur = $cryptocurrencyRepository->findOneBy(['libelleCoingecko' => 'jarvis-synthetic-euro']);
        $ratioUsdEur = ($jeur !== null && $jeur->getPriceUsd() !== null) ? $jeur->getPriceUsd() : 1.04;

        foreach ($positions as $key => $value) {
            $valueUsd = $value['totalsum'] * $value['priceUsd'];
            $valueEur = $valueUsd / $ratioUsdEur;
            $value['valueUsd'] = $valueUsd;
            $value['valueEur'] = $valueEur;
            $positions[$key] = $value;
            $totalUsd += $valueUsd;
            $totalEur += $valueEur;
        }

        foreach ($positions_stable as $key => $value) {
            $valueUsd = $value['totalsum'] * $value['priceUsd'];
            $valueEur = $valueUsd / $ratioUsdEur;
            $value['valueUsd'] = $valueUsd;
            $value['valueEur'] = $valueEur;
            $positions_stable[$key] = $value;
            $totalUsdStable += $valueUsd;
            $totalEurStable += $valueEur;
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

        $totalYearFarming = 0;

        foreach ($strategies as $strategy) {
            $value_dollar = $strategy->getCoin()->getPriceUsd() * $strategy->getNbCoins();
            $totalYearFarming += $strategy->getApr() * $value_dollar / 100;
        }

        foreach ($strategies_lp as $strategy) {
            $value_dollar = $strategy->getCoin1()->getPriceUsd() * $strategy->getNbCoin1() + $strategy->getCoin2()->getPriceUsd() * $strategy->getNbCoin2();
            $totalYearFarming += $strategy->getApr() * $value_dollar / 100;
        }

        $totalLoan = $loanRepository->getTotal($this->getUser());

        return $this->render('cryptobook/index.html.twig', [
            'positions' => $positions,
            'positions_stable' => $positions_stable,
            'totalDepositEur' => $totalDepositEur,
            'totalUsd' => $totalUsd,
            'totalEur' => $totalEur,
            'totalUsdStable' => $totalUsdStable,
            'totalEurStable' => $totalEurStable,
            'totalYearFarmingUsd' => $totalYearFarming,
            'ratioUsdEur' => $ratioUsdEur,
            'totalLoan' => $totalLoan,
        ]);
    }
}
