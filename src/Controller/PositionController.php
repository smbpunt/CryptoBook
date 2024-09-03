<?php

namespace App\Controller;

use App\Entity\Position;
use App\Form\PositionType;
use App\Repository\CryptocurrencyRepository;
use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/position')]
class PositionController extends AbstractController
{
    #[Route('/', name: 'app_position_index', methods: ['GET'])]
    public function index(PositionRepository $positionRepository, CryptocurrencyRepository $cryptocurrencyRepository): Response
    {
        // Liste des positions avec calcul de la somme
        $positions_sum = $cryptocurrencyRepository->getSumCoinByUser($this->getUser()) ?? [];

        $map = [];

        foreach ($positions_sum as $byCoin) {
            // coin en cours
            $coin = $byCoin['coin'];

            // On récupére la liste des positions pour ce coin
            $positionsByCoin = $positionRepository->getPositions($this->getUser(), false, true, $coin);

            // J'initialise à 0
            $coutMoyen = 0;

            //Je boucle sur les positions du coin en cours
            foreach ($positionsByCoin as $position) {
                // % d'allocation sur le coin, par rapport au nombre total
                $percent = $position->getRemainingCoins() / $byCoin['totalsum'];

                // J'incrémente les cout moyen, en fonction du % d'allocation
                $coutMoyen += $position->getEntryCoinValue() * $percent;
            }

            $map[] = [
                'grouped' => $byCoin,
                'coutMoyen' => $coutMoyen,
                'splitted' => $positionsByCoin
            ];
        }

        return $this->render('position/index.html.twig', [
            'positions' => $map,
            'positions_stable' => $positionRepository->getPositions($this->getUser(), true),
            'positions_closed' => $positionRepository->getPositions($this->getUser(), false, false)
        ]);
    }


    #[Route('/{id}/infos', name: 'app_position_ajax_infos', methods: ['POST'])]
    public function ajax(Position $position): Response
    {
        if ($position->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('position/_strategy_position.html.twig', [
            'position' => $position,
        ]);
    }

    #[Route('/new', name: 'app_position_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PositionRepository $positionRepository): Response
    {
        $position = new Position($this->getUser());
        $form = $this->createForm(PositionType::class, $position);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $positionRepository->add($position, true);

            //TODO submit & next
            return $this->redirectToRoute('app_position_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('position/new.html.twig', [
            'position' => $position,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_position_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Position $position, PositionRepository $positionRepository): Response
    {
        if ($position->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_position_index');
        }

        $form = $this->createForm(PositionType::class, $position);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $positionRepository->add($position, true);

            return $this->redirectToRoute('app_position_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('position/edit.html.twig', [
            'position' => $position,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_position_delete', methods: ['POST'])]
    public function delete(Request $request, Position $position, PositionRepository $positionRepository): Response
    {
        if ($position->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_position_index');
        }

        if ($this->isCsrfTokenValid('delete' . $position->getId(), $request->request->get('_token'))) {
            $positionRepository->remove($position, true);
        }

        return $this->redirectToRoute('app_position_index', [], Response::HTTP_SEE_OTHER);
    }
}
