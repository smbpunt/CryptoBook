<?php

namespace App\Controller;

use App\Entity\StrategyLp;
use App\Form\StrategyLpType;
use App\Repository\StrategyLpRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/strategy/lp')]
class StrategyLpController extends AbstractController
{
    #[Route('/{id}/infos', name: 'app_strategy_lp_ajax_infos', methods: ['POST'])]
    public function ajax(StrategyLp $strategyLp): Response
    {
        if ($strategyLp->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('strategy_lp/_informations.twig', [
            'farming' => $strategyLp,
        ]);
    }

    #[Route('/new', name: 'app_strategy_lp_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StrategyLpRepository $strategyLpRepository): Response
    {
        $strategyLp = new StrategyLp($this->getUser());
        $form = $this->createForm(StrategyLpType::class, $strategyLp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $strategyLpRepository->add($strategyLp, true);

            return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_lp/new.html.twig', [
            'strategy_lp' => $strategyLp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_strategy_lp_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StrategyLp $strategyLp, StrategyLpRepository $strategyLpRepository): Response
    {
        if ($strategyLp->getOwner() !== $this->getUser()) {
            return $this->redirectToRoute('app_strategy_farming_index');
        }

        $form = $this->createForm(StrategyLpType::class, $strategyLp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $strategyLpRepository->add($strategyLp, true);

            return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_lp/edit.html.twig', [
            'strategy_lp' => $strategyLp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_strategy_lp_delete', methods: ['POST'])]
    public function delete(Request $request, StrategyLp $strategyLp, StrategyLpRepository $strategyLpRepository): Response
    {
        if ($strategyLp->getOwner() !== $this->getUser()) {
            return $this->redirectToRoute('app_strategy_farming_index');
        }

        if ($this->isCsrfTokenValid('delete'.$strategyLp->getId(), $request->request->get('_token'))) {
            $strategyLpRepository->remove($strategyLp, true);
        }

        return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
    }
}
