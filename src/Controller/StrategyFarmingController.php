<?php

namespace App\Controller;

use App\Entity\StrategyFarming;
use App\Form\StrategyFarmingType;
use App\Repository\StrategyFarmingRepository;
use App\Repository\StrategyLpRepository;
use App\Service\FarmingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/strategy/farming')]
class StrategyFarmingController extends AbstractController
{
    #[Route('/', name: 'app_strategy_farming_index', methods: ['GET'])]
    public function index(StrategyFarmingRepository $strategyFarmingRepository, StrategyLpRepository $strategyLpRepository, FarmingService $farmingService): Response
    {
        $strategies = $strategyFarmingRepository->findByStable($this->getUser(), false);
        $strategies_stable = $strategyFarmingRepository->findByStable($this->getUser(), true);
        $strategies_lp = $strategyLpRepository->findBy([
            'owner' => $this->getUser()
        ]);

        $total_year_singleasset = $farmingService->getTotalFarmingSimpleByYearUsd(checkBooleanIsStable: true, isStable: false);
        $total_year_stable = $farmingService->getTotalFarmingSimpleByYearUsd(checkBooleanIsStable: true, isStable: true);
        $total_year_lp = $farmingService->getTotalFarmingLpByYearUsd();

        return $this->render('strategy_farming/index.html.twig', [
            'strategy_farmings' => $strategies,
            'strategy_farmings_stable' => $strategies_stable,
            'strategy_farmings_lp' => $strategies_lp,
            'total_year' => $total_year_singleasset,
            'total_year_stable' => $total_year_stable,
            'total_year_lp' => $total_year_lp,
            'total_total_year' => $total_year_singleasset + $total_year_stable + $total_year_lp
        ]);
    }

    #[Route('/{id}/infos', name: 'app_strategy_farming_ajax_infos', methods: ['POST'])]
    public function ajax(StrategyFarming $strategyFarming): Response
    {
        if ($strategyFarming->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('strategy_farming/_informations.twig', [
            'farming' => $strategyFarming,
        ]);
    }

    #[Route('/new', name: 'app_strategy_farming_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StrategyFarmingRepository $strategyFarmingRepository): Response
    {
        $strategyFarming = new StrategyFarming($this->getUser());
        $form = $this->createForm(StrategyFarmingType::class, $strategyFarming);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $strategyFarmingRepository->add($strategyFarming, true);

            return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_farming/new.html.twig', [
            'strategy_farming' => $strategyFarming,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_strategy_farming_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StrategyFarming $strategyFarming, StrategyFarmingRepository $strategyFarmingRepository): Response
    {
        if ($strategyFarming->getOwner() !== $this->getUser()) {
            return $this->redirectToRoute('app_strategy_farming_index');
        }

        $form = $this->createForm(StrategyFarmingType::class, $strategyFarming);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $strategyFarmingRepository->add($strategyFarming, true);

            return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_farming/edit.html.twig', [
            'strategy_farming' => $strategyFarming,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_strategy_farming_delete', methods: ['POST'])]
    public function delete(Request $request, StrategyFarming $strategyFarming, StrategyFarmingRepository $strategyFarmingRepository): Response
    {
        if ($strategyFarming->getOwner() !== $this->getUser()) {
            return $this->redirectToRoute('app_strategy_farming_index');
        }

        if ($this->isCsrfTokenValid('delete'.$strategyFarming->getId(), $request->request->get('_token'))) {
            $strategyFarmingRepository->remove($strategyFarming, true);
        }

        return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
    }
}
