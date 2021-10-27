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
        $positions = $this->getUser() ? $positionRepository->getSumCoinByUser($this->getUser()) : [];
        $totalDepositEur = $this->getUser() ? $depositRepository->getTotal($this->getUser()) : [];
        $totalUsd = 0;
        $totalEur = 0;
        foreach ($positions as $key => $value) {
            $valueUsd = $value['totalsum'] * $value['priceUsd'];
            $valueEur = $value['totalsum'] * $value['priceEur'];
            $value['valueUsd'] = $valueUsd;
            $value['valueEur'] = $valueEur;
            $positions[$key] = $value;
            $totalUsd += $valueUsd;
            $totalEur += $valueEur;
        }

        return $this->render('cryptobook/index.html.twig', [
            'positions' => $positions,
            'totalDepositEur' => $totalDepositEur,
            'totalUsd' => $totalUsd,
            'totalEur' => $totalEur,
        ]);
    }
}
