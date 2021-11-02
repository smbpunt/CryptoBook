<?php

namespace App\Controller;

use App\Entity\Position;
use App\Form\PositionType;
use App\Repository\PositionRepository;
use App\Service\PositionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/position")
 */
class PositionController extends AbstractController
{
    /**
     * @Route("/", name="position_index", methods={"GET"})
     */
    public function index(PositionRepository $positionRepository): Response
    {
        return $this->render('position/index.html.twig', [
            'positions' => $positionRepository->getPositions($this->getUser(), false),
            'positions_stable' => $positionRepository->getPositions($this->getUser(), true)
        ]);
    }

    /**
     * @Route("/new", name="position_new", methods={"GET","POST"})
     */
    public function new(Request $request, PositionService $service): Response
    {
        $position = new Position($this->getUser());
        $form = $this->createForm(PositionType::class, $position);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $service->calculateRemainingCoins($position);
            $entityManager->persist($position);
            $entityManager->flush();

            return $form->get('submitAndNext')->isClicked() ? $this->redirectToRoute('position_new') : $this->redirectToRoute('position_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('position/new.html.twig', [
            'position' => $position,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="position_show", methods={"GET"})
     */
    public function show(Position $position): Response
    {
        return $this->render('position/show.html.twig', [
            'position' => $position,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="position_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Position $position, PositionService $service): Response
    {
        if ($position->getUser() !== $this->getUser()) {
            $this->redirectToRoute('position_index');
        }

        $form = $this->createForm(PositionType::class, $position);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->calculateRemainingCoins($position);
            $this->getDoctrine()->getManager()->flush();
            return $form->get('submitAndNext')->isClicked() ? $this->redirectToRoute('position_new') : $this->redirectToRoute('position_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('position/edit.html.twig', [
            'position' => $position,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="position_delete", methods={"POST"})
     */
    public function delete(Request $request, Position $position): Response
    {
        if ($position->getUser() !== $this->getUser()) {
            $this->redirectToRoute('position_index');
        }

        if ($this->isCsrfTokenValid('delete' . $position->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($position);
            $entityManager->flush();
        }

        return $this->redirectToRoute('position_index', [], Response::HTTP_SEE_OTHER);
    }
}
