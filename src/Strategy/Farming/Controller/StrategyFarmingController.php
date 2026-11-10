<?php

namespace App\Strategy\Farming\Controller;

use App\Strategy\Farming\Builder\FarmingOverviewBuilder;
use App\Strategy\Farming\Entity\StrategyFarming;
use App\Strategy\Farming\Form\StrategyFarmingType;
use App\Strategy\Farming\Repository\StrategyFarmingRepository;
use App\User\Entity\User;
use App\User\Voter\OwnerVoter;
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
    public function index(FarmingOverviewBuilder $farmingOverviewBuilder, #[CurrentUser] User $user): Response
    {
        return $this->render('strategy/farming/index.html.twig', [
            'overview' => $farmingOverviewBuilder->build($user),
        ]);
    }

    #[Route('/{id}/infos', name: 'app_strategy_farming_ajax_infos', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'strategyFarming')]
    public function ajax(StrategyFarming $strategyFarming): Response
    {
        return $this->render('strategy/farming/_informations.twig', [
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

        return $this->render('strategy/farming/new.html.twig', [
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

        return $this->render('strategy/farming/edit.html.twig', [
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
