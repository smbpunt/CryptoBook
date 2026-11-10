<?php

namespace App\Strategy\Dca\Controller;

use App\Cryptocurrency\Repository\CryptocurrencyRepository;
use App\FiatCurrency\Converter\FiatConverter;
use App\Position\Repository\PositionRepository;
use App\Service\DcaService;
use App\Strategy\Dca\Dto\DcaAutoQuery;
use App\Strategy\Dca\Entity\StrategyDca;
use App\Strategy\Dca\Form\DcaAutoType;
use App\Strategy\Dca\Form\DcaToPositionType;
use App\Strategy\Dca\Form\StrategyDcaType;
use App\Strategy\Dca\Repository\StrategyDcaRepository;
use App\User\Entity\User;
use App\User\Resolver\FavoriteCurrencyResolver;
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
    public function index(StrategyDcaRepository $strategyDcaRepository, CryptocurrencyRepository $cryptocurrencyRepository, FiatConverter $fiatConverter, FavoriteCurrencyResolver $favoriteCurrencyResolver, #[CurrentUser] User $user): Response
    {
        $dcaUser = $strategyDcaRepository->findOneBy([
            'owner' => $user,
        ]);

        $form = $this->createForm(DcaAutoType::class);

        return $this->render('strategy/dca/index.html.twig', [
            'dca' => $dcaUser,
            'fiatToDcaUsd' => null === $dcaUser ? 0.0 : $fiatConverter->toUsd((float) $dcaUser->getFiatToDcaEur(), $favoriteCurrencyResolver->resolve($user)),
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

        return $this->render('strategy/dca/new.html.twig', [
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

        return $this->render('strategy/dca/generatePositions.html.twig', [
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

        return $this->render('strategy/dca/generatePositions.html.twig', [
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

        return $this->render('strategy/dca/edit.html.twig', [
            'strategy_dca' => $strategyDca,
            'form' => $form,
        ]);
    }
}
