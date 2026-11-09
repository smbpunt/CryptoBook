<?php

namespace App\Controller;

use App\Entity\StrategyFarming;
use App\Entity\User;
use App\Form\StrategyFarmingType;
use App\Repository\StrategyFarmingRepository;
use App\Repository\StrategyLpRepository;
use App\Security\Voter\OwnerVoter;
use App\Service\FarmingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/strategy/farming')]
class StrategyFarmingController extends AbstractController
{
    #[Route('/', name: 'app_strategy_farming_index', methods: ['GET'])]
    public function index(StrategyFarmingRepository $strategyFarmingRepository, StrategyLpRepository $strategyLpRepository, FarmingService $farmingService, #[CurrentUser] User $user): Response
    {
        $strategies = $strategyFarmingRepository->findByStable($user, false);
        $strategies_stable = $strategyFarmingRepository->findByStable($user, true);
        $strategies_lp = $strategyLpRepository->findBy([
            'owner' => $user,
        ]);

        $total_year_singleasset = $farmingService->getTotalFarmingSimpleByYearUsd($user, isStable: false);
        $total_year_stable = $farmingService->getTotalFarmingSimpleByYearUsd($user, isStable: true);
        $total_year_lp = $farmingService->getTotalFarmingLpByYearUsd($user);

        return $this->render('strategy_farming/index.html.twig', [
            'strategy_farmings' => $strategies,
            'strategy_farmings_stable' => $strategies_stable,
            'strategy_farmings_lp' => $strategies_lp,
            'total_year' => $total_year_singleasset,
            'total_year_stable' => $total_year_stable,
            'total_year_lp' => $total_year_lp,
            'total_total_year' => $total_year_singleasset + $total_year_stable + $total_year_lp,
        ]);
    }

    #[Route('/{id}/infos', name: 'app_strategy_farming_ajax_infos', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'strategyFarming')]
    public function ajax(StrategyFarming $strategyFarming): Response
    {
        return $this->render('strategy_farming/_informations.twig', [
            'farming' => $strategyFarming,
        ]);
    }

    #[Route('/new', name: 'app_strategy_farming_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StrategyFarmingRepository $strategyFarmingRepository, #[CurrentUser] User $user): Response
    {
        $strategyFarming = new StrategyFarming($user);
        $form = $this->createForm(StrategyFarmingType::class, $strategyFarming);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $strategyFarmingRepository->add($strategyFarming, true);

            return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('strategy_farming/new.html.twig', [
            'strategy_farming' => $strategyFarming,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_strategy_farming_edit', methods: ['GET', 'POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'strategyFarming')]
    public function edit(Request $request, StrategyFarming $strategyFarming, StrategyFarmingRepository $strategyFarmingRepository): Response
    {
        $form = $this->createForm(StrategyFarmingType::class, $strategyFarming);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $strategyFarmingRepository->add($strategyFarming, true);

            return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('strategy_farming/edit.html.twig', [
            'strategy_farming' => $strategyFarming,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_strategy_farming_delete', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'strategyFarming')]
    #[IsCsrfTokenValid(new Expression('"delete" ~ args["strategyFarming"].getId()'))]
    public function delete(StrategyFarming $strategyFarming, StrategyFarmingRepository $strategyFarmingRepository): Response
    {
        $strategyFarmingRepository->remove($strategyFarming, true);

        return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
    }
}
