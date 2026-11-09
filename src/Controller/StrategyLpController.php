<?php

namespace App\Controller;

use App\Entity\StrategyLp;
use App\Entity\User;
use App\Form\StrategyLpType;
use App\Repository\StrategyLpRepository;
use App\Security\Voter\OwnerVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/strategy/lp')]
class StrategyLpController extends AbstractController
{
    #[Route('/{id}/infos', name: 'app_strategy_lp_ajax_infos', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'strategyLp')]
    public function ajax(StrategyLp $strategyLp): Response
    {
        return $this->render('strategy_lp/_informations.twig', [
            'farming' => $strategyLp,
        ]);
    }

    #[Route('/new', name: 'app_strategy_lp_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StrategyLpRepository $strategyLpRepository, #[CurrentUser] User $user): Response
    {
        $strategyLp = new StrategyLp($user);
        $form = $this->createForm(StrategyLpType::class, $strategyLp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $strategyLpRepository->add($strategyLp, true);

            return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('strategy_lp/new.html.twig', [
            'strategy_lp' => $strategyLp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_strategy_lp_edit', methods: ['GET', 'POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'strategyLp')]
    public function edit(Request $request, StrategyLp $strategyLp, StrategyLpRepository $strategyLpRepository): Response
    {
        $form = $this->createForm(StrategyLpType::class, $strategyLp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $strategyLpRepository->add($strategyLp, true);

            return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('strategy_lp/edit.html.twig', [
            'strategy_lp' => $strategyLp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_strategy_lp_delete', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'strategyLp')]
    #[IsCsrfTokenValid(new Expression('"delete" ~ args["strategyLp"].getId()'))]
    public function delete(StrategyLp $strategyLp, StrategyLpRepository $strategyLpRepository): Response
    {
        $strategyLpRepository->remove($strategyLp, true);

        return $this->redirectToRoute('app_strategy_farming_index', [], Response::HTTP_SEE_OTHER);
    }
}
