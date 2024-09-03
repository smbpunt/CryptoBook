<?php

namespace App\Controller;

use App\Entity\StrategyDca;
use App\Form\DcaAutoType;
use App\Form\DcaToPositionType;
use App\Form\StrategyDcaType;
use App\Repository\CryptocurrencyRepository;
use App\Repository\PositionRepository;
use App\Repository\StrategyDcaRepository;
use App\Service\DcaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/strategy/dca')]
class StrategyDcaController extends AbstractController
{
    #[Route('/', name: 'app_strategy_dca_index', methods: ['GET'])]
    public function index(StrategyDcaRepository $strategyDcaRepository, CryptocurrencyRepository $cryptocurrencyRepository): Response
    {
        $dcaUser = $strategyDcaRepository->findOneBy([
            'owner' => $this->getUser()
        ]);

        $form = $this->createForm(DcaAutoType::class);

        return $this->render('strategy_dca/index.html.twig', [
            'dca' => $dcaUser,
            'form' => $form->createView()
        ]);
    }

    #[Route('/new', name: 'app_strategy_dca_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StrategyDcaRepository $strategyDcaRepository): Response
    {
        $strategyDca = $strategyDcaRepository->findOneBy([
            'owner' => $this->getUser()
        ]);

        if ($strategyDca) {
            return $this->redirectToRoute('app_strategy_dca_edit');
        }

        $strategyDca = new StrategyDca($this->getUser());
        $form = $this->createForm(StrategyDcaType::class, $strategyDca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $strategyDcaRepository->add($strategyDca, true);

            return $this->redirectToRoute('app_strategy_dca_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_dca/new.html.twig', [
            'strategy_dca' => $strategyDca,
            'form' => $form,
        ]);
    }

    #[Route('/generatePositions/{invested}', name: 'app_strategy_dca_generate_positions', methods: ['GET', 'POST'])]
    public function generatePositions(Request $request, StrategyDcaRepository $strategyDcaRepository, PositionRepository $positionRepository, DcaService $dcaService, CryptocurrencyRepository $cryptocurrencyRepository, float $invested = 0): Response
    {
        $strategyDca = $strategyDcaRepository->findOneBy([
            'owner' => $this->getUser()
        ]);

        if (is_null($strategyDca)) {
            return $this->redirectToRoute('app_strategy_dca_new');
        }

        $jeur = $cryptocurrencyRepository->findOneBy(['libelleCoingecko' => 'jarvis-synthetic-euro']);
        $ratioUsdEur = ($jeur !== null && $jeur->getPriceUsd() !== null) ? $jeur->getPriceUsd() : 1.04;
        $valueDca = $invested > 0 ? $invested : ($strategyDca->getFiatToDcaEur() * $ratioUsdEur + $strategyDca->getFarmingToDcaUsd());

        $positions = $dcaService->generatePositions($strategyDca, $this->getUser(), $valueDca);

        $form = $this->createForm(DcaToPositionType::class, null, [
            'positions' => $positions
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($positions as $position) {
                $positionRepository->add($position, true);
            }

            return $this->redirectToRoute('app_position_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_dca/generatePositions.html.twig', [
            'form_dca' => $form,
        ]);

    }


    #[Route('/dcaAuto', name: 'app_strategy_dca_dcaauto', methods: ['GET', 'POST'])]
    public function dcaAuto(Request $request, PositionRepository $positionRepository, DcaService $dcaService): Response
    {
        if ($request->get('crypto') === null || $request->get('t') === null || $request->get('nb') === null || $request->get('total') === null || $request->get('d') === null) {
            //todo erreur
            return $this->redirectToRoute('app_strategy_dca_index');
        }

        $positions = $dcaService->generateDcaAutoPositions($this->getUser(), $request->get('crypto'), $request->get('t'), $request->get('nb'), $request->get('total'), $request->get('d'));

        $form = $this->createForm(DcaToPositionType::class, null, [
            'positions' => $positions
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($positions as $position) {
                $positionRepository->add($position, true);
            }

            return $this->redirectToRoute('app_position_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_dca/generatePositions.html.twig', [
            'form_dca' => $form,
        ]);
    }

    #[Route('/edit', name: 'app_strategy_dca_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StrategyDcaRepository $strategyDcaRepository): Response
    {
        $strategyDca = $strategyDcaRepository->findOneBy([
            'owner' => $this->getUser()
        ]);

        if (is_null($strategyDca)) {
            return $this->redirectToRoute('app_strategy_dca_new');
        }

        $form = $this->createForm(StrategyDcaType::class, $strategyDca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $strategyDcaRepository->add($strategyDca, true);

            return $this->redirectToRoute('app_strategy_dca_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_dca/edit.html.twig', [
            'strategy_dca' => $strategyDca,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_strategy_dca_delete', methods: ['POST'])]
    public function delete(Request $request, StrategyDca $strategyDca, StrategyDcaRepository $strategyDcaRepository): Response
    {
        if ($strategyDca->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_strategy_dca_index');
        }

        if ($this->isCsrfTokenValid('delete' . $strategyDca->getId(), $request->request->get('_token'))) {
            $strategyDcaRepository->remove($strategyDca, true);
        }

        return $this->redirectToRoute('app_strategy_dca_index', [], Response::HTTP_SEE_OTHER);
    }
}
