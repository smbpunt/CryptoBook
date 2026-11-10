<?php

namespace App\Strategy\Dca\Controller;

use App\Cryptocurrency\Repository\CryptocurrencyRepository;
use App\Position\Entity\Position;
use App\Strategy\Dca\Calculator\DcaAmountCalculator;
use App\Strategy\Dca\Dto\DcaAutoQuery;
use App\Strategy\Dca\Entity\StrategyDca;
use App\Strategy\Dca\Form\DcaAutoType;
use App\Strategy\Dca\Form\DcaToPositionType;
use App\Strategy\Dca\Form\StrategyDcaType;
use App\Strategy\Dca\Generator\DcaAutoPositionGenerator;
use App\Strategy\Dca\Generator\DcaPositionGenerator;
use App\Strategy\Dca\Repository\StrategyDcaRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/strategy/dca')]
class StrategyDcaController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'app_strategy_dca_index', methods: ['GET'])]
    public function index(StrategyDcaRepository $strategyDcaRepository, DcaAmountCalculator $amountCalculator, #[CurrentUser] User $user): Response
    {
        $dca = $strategyDcaRepository->findOneByOwner($user);

        return $this->render('strategy/dca/index.html.twig', [
            'dca' => $dca,
            'amountUsd' => null === $dca ? 0.0 : $amountCalculator->amountUsd($dca),
            'form' => $this->createForm(DcaAutoType::class),
        ]);
    }

    #[Route('/new', name: 'app_strategy_dca_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StrategyDcaRepository $strategyDcaRepository, #[CurrentUser] User $user): Response
    {
        if (null !== $strategyDcaRepository->findOneByOwner($user)) {
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
    public function generatePositions(Request $request, StrategyDcaRepository $strategyDcaRepository, DcaAmountCalculator $amountCalculator, DcaPositionGenerator $generator, float $invested, #[CurrentUser] User $user): Response
    {
        $strategyDca = $strategyDcaRepository->findOneByOwner($user);
        if (null === $strategyDca) {
            return $this->redirectToRoute('app_strategy_dca_new');
        }

        $amountUsd = $invested > 0 ? $invested : $amountCalculator->amountUsd($strategyDca);
        // Nothing to split: the index shows why (no amount, or no EUR rate).
        if (null === $amountUsd || $amountUsd <= 0) {
            return $this->redirectToRoute('app_strategy_dca_index');
        }

        return $this->savePositions($request, $generator->generate($strategyDca, $user, $amountUsd));
    }

    #[Route('/dcaAuto', name: 'app_strategy_dca_dcaauto', methods: ['GET', 'POST'])]
    public function dcaAuto(Request $request, CryptocurrencyRepository $cryptocurrencyRepository, DcaAutoPositionGenerator $generator, #[CurrentUser] User $user, #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)] ?DcaAutoQuery $query = null): Response
    {
        if (null === $query) {
            return $this->redirectToRoute('app_strategy_dca_index');
        }

        $coin = $cryptocurrencyRepository->find($query->coinId) ?? throw $this->createNotFoundException('Unknown coin.');

        return $this->savePositions($request, $generator->generate($user, $coin, $query->recurrence, $query->count, $query->total, $query->firstDate));
    }

    #[Route('/edit', name: 'app_strategy_dca_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StrategyDcaRepository $strategyDcaRepository, #[CurrentUser] User $user): Response
    {
        $strategyDca = $strategyDcaRepository->findOneByOwner($user);
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

    /**
     * Shows the generated positions to adjust them, and saves them in one flush.
     *
     * @param list<Position> $positions
     */
    private function savePositions(Request $request, array $positions): Response
    {
        $form = $this->createForm(DcaToPositionType::class, null, [
            'positions' => $positions,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($positions as $position) {
                $this->entityManager->persist($position);
            }
            $this->entityManager->flush();

            return $this->redirectToRoute('app_position_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('strategy/dca/generatePositions.html.twig', [
            'form_dca' => $form,
        ]);
    }
}
