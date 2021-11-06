<?php

namespace App\Controller;

use App\Repository\DepositRepository;
use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class CryptobookController extends AbstractController
{
    /**
     * @Route("", name="home")
     */
    public function index(PositionRepository $positionRepository, DepositRepository $depositRepository): Response
    {
        $positions = $positionRepository->getSumCoinByUser($this->getUser()) ?? [];
        $positions_stable = $positionRepository->getSumCoinByUser($this->getUser(), true) ?? [];
        $totalDepositEur = $depositRepository->getTotal($this->getUser()) ?? 0.0;
        $totalUsd = 0;
        $totalEur = 0;
        $totalUsdStable = 0;
        $totalEurStable = 0;
        foreach ($positions as $key => $value) {
            $valueUsd = $value['totalsum'] * $value['priceUsd'];
            $valueEur = $value['totalsum'] * $value['priceEur'];
            $value['valueUsd'] = $valueUsd;
            $value['valueEur'] = $valueEur;
            $positions[$key] = $value;
            $totalUsd += $valueUsd;
            $totalEur += $valueEur;
        }


        foreach ($positions_stable as $key => $value) {
            $valueUsd = $value['totalsum'] * $value['priceUsd'];
            $valueEur = $value['totalsum'] * $value['priceEur'];
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

        return $this->render('cryptobook/index.html.twig', [
            'positions' => $positions,
            'positions_stable' => $positions_stable,
            'totalDepositEur' => $totalDepositEur,
            'totalUsd' => $totalUsd,
            'totalEur' => $totalEur,
            'totalUsdStable' => $totalUsdStable,
            'totalEurStable' => $totalEurStable,
        ]);
    }
}
