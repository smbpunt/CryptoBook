<?php

namespace App\Controller;

use App\Dto\DcaAutoQuery;
use App\Entity\StrategyDca;
use App\Entity\User;
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
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/strategy/dca')]
class StrategyDcaController extends AbstractController
{
    #[Route('/', name: 'app_strategy_dca_index', methods: ['GET'])]
    public function index(StrategyDcaRepository $strategyDcaRepository, CryptocurrencyRepository $cryptocurrencyRepository, #[CurrentUser] User $user): Response
    {
        $dcaUser = $strategyDcaRepository->findOneBy([
            'owner' => $user,
        ]);

        $form = $this->createForm(DcaAutoType::class);

        return $this->render('strategy_dca/index.html.twig', [
            'dca' => $dcaUser,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_strategy_dca_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StrategyDcaRepository $strategyDcaRepository, #[CurrentUser] User $user): Response
    {
        $strategyDca = $strategyDcaRepository->findOneBy([
            'owner' => $user,
        ]);

        if ($strategyDca) {
            return $this->redirectToRoute('app_strategy_dca_edit');
        }

        $strategyDca = new StrategyDca($user);
        $form = $this->createForm(StrategyDcaType::class, $strategyDca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $strategyDcaRepository->add($strategyDca, true);

            return $this->redirectToRoute('app_strategy_dca_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('strategy_dca/new.html.twig', [
            'strategy_dca' => $strategyDca,
            'form' => $form,
        ]);
    }

    #[Route('/generatePositions/{invested<\d+(\.\d+)?>?0}', name: 'app_strategy_dca_generate_positions', methods: ['GET', 'POST'])]
    public function generatePositions(Request $request, StrategyDcaRepository $strategyDcaRepository, PositionRepository $positionRepository, DcaService $dcaService, CryptocurrencyRepository $cryptocurrencyRepository, float $invested, #[CurrentUser] User $user): Response
    {
        $strategyDca = $strategyDcaRepository->findOneBy([
            'owner' => $user,
        ]);

        if (null === $strategyDca) {
            return $this->redirectToRoute('app_strategy_dca_new');
        }

        $jeur = $cryptocurrencyRepository->findOneBy(['libelleCoingecko' => 'jarvis-synthetic-euro']);
        $ratioUsdEur = (null !== $jeur && null !== $jeur->getPriceUsd()) ? $jeur->getPriceUsd() : 1.04;
        $valueDca = $invested > 0 ? $invested : ($strategyDca->getFiatToDcaEur() * $ratioUsdEur + $strategyDca->getFarmingToDcaUsd());

        $positions = $dcaService->generatePositions($strategyDca, $user, $valueDca);

        $form = $this->createForm(DcaToPositionType::class, null, [
            'positions' => $positions,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($positions as $position) {
                $positionRepository->add($position, true);
            }

            return $this->redirectToRoute('app_position_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('strategy_dca/generatePositions.html.twig', [
            'form_dca' => $form,
        ]);
    }

    #[Route('/dcaAuto', name: 'app_strategy_dca_dcaauto', methods: ['GET', 'POST'])]
    public function dcaAuto(Request $request, PositionRepository $positionRepository, DcaService $dcaService, #[CurrentUser] User $user, #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)] ?DcaAutoQuery $query = null): Response
    {
        if (null === $query) {
            return $this->redirectToRoute('app_strategy_dca_index');
        }

        $positions = $dcaService->generateDcaAutoPositions($user, $query->coinId, $query->recurrence, $query->count, $query->total, $query->firstDate);

        $form = $this->createForm(DcaToPositionType::class, null, [
            'positions' => $positions,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($positions as $position) {
                $positionRepository->add($position, true);
            }

            return $this->redirectToRoute('app_position_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('strategy_dca/generatePositions.html.twig', [
            'form_dca' => $form,
        ]);
    }

    #[Route('/edit', name: 'app_strategy_dca_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StrategyDcaRepository $strategyDcaRepository, #[CurrentUser] User $user): Response
    {
        $strategyDca = $strategyDcaRepository->findOneBy([
            'owner' => $user,
        ]);

        if (null === $strategyDca) {
            return $this->redirectToRoute('app_strategy_dca_new');
        }

        $form = $this->createForm(StrategyDcaType::class, $strategyDca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $strategyDcaRepository->add($strategyDca, true);

            return $this->redirectToRoute('app_strategy_dca_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('strategy_dca/edit.html.twig', [
            'strategy_dca' => $strategyDca,
            'form' => $form,
        ]);
    }
}
