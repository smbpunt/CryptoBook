<?php

namespace App\Controller;

use App\Entity\StrategyDca;
use App\Form\StrategyDcaType;
use App\Repository\StrategyDcaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/strategy/dca")
 */
class StrategyDcaController extends AbstractController
{
    /**
     * @Route("/", name="strategy_dca_index", methods={"GET"})
     */
    public function index(StrategyDcaRepository $strategyDcaRepository): Response
    {
        $dcaUser = $this->getUser() ? $strategyDcaRepository->findOneBy([
            'user' => $this->getUser()
        ]) : null;

        return $this->render('strategy_dca/index.html.twig', [
            'dca' => $dcaUser,
        ]);
    }

    /**
     * @Route("/new", name="strategy_dca_new", methods={"GET","POST"})
     */
    public function new(Request $request, StrategyDcaRepository $strategyDcaRepository): Response
    {
        $strategyDca = $strategyDcaRepository->findOneBy([
            'user' => $this->getUser()
        ]);

        if ($strategyDca) {
            return $this->redirectToRoute('strategy_dca_edit');
        }

        $strategyDca = new StrategyDca($this->getUser());
        $form = $this->createForm(StrategyDcaType::class, $strategyDca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($strategyDca);
            $entityManager->flush();

            return $this->redirectToRoute('strategy_dca_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_dca/new.html.twig', [
            'strategy_dca' => $strategyDca,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/edit", name="strategy_dca_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, StrategyDcaRepository $strategyDcaRepository): Response
    {
        $strategyDca = $strategyDcaRepository->findOneBy([
            'user' => $this->getUser()
        ]);

        if (is_null($strategyDca)) {
            return $this->redirectToRoute('strategy_dca_new');
        }

        $form = $this->createForm(StrategyDcaType::class, $strategyDca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('strategy_dca_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_dca/edit.html.twig', [
            'strategy_dca' => $strategyDca,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="strategy_dca_delete", methods={"POST"})
     */
    public function delete(Request $request, StrategyDca $strategyDca): Response
    {
        if ($strategyDca->getUser() !== $this->getUser()) {
            $this->redirectToRoute('strategy_dca_index');
        }

        if ($this->isCsrfTokenValid('delete' . $strategyDca->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($strategyDca);
            $entityManager->flush();
        }

        return $this->redirectToRoute('strategy_dca_index', [], Response::HTTP_SEE_OTHER);
    }
}
