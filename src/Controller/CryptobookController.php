<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\LoanRepository;
use App\Service\DepositService;
use App\Service\FarmingService;
use App\Wallet\Calculator\WalletValueCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class CryptobookController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(WalletValueCalculator $walletValueCalculator, DepositService $depositService, LoanRepository $loanRepository, FarmingService $farmingService, #[CurrentUser] User $user): Response
    {
        return $this->render('cryptobook/index.html.twig', [
            'wallet' => $walletValueCalculator->calculate($user),
            'totalDepositUsd' => $depositService->getTotalDepositUsd($user),
            'totalYearFarmingUsd' => $farmingService->getTotalFarmingByYearUsd($user),
            'totalLoanUsd' => $loanRepository->getTotal($user),
        ]);
    }
}
