<?php

namespace App\Controller;

use App\Entity\StrategyLP;
use App\Form\StrategyLPType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/strategy/lp")
 */
class StrategyLPController extends AbstractController
{

    /**
     * @Route("/new", name="strategy_l_p_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $strategyLP = new StrategyLP($this->getUser());
        $form = $this->createForm(StrategyLPType::class, $strategyLP);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($strategyLP);
            $entityManager->flush();

            return $this->redirectToRoute('strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_lp/new.html.twig', [
            'strategy_l_p' => $strategyLP,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="strategy_l_p_show", methods={"GET"})
     */
    public function show(StrategyLP $strategyLP): Response
    {
        return $this->render('strategy_lp/show.html.twig', [
            'strategy_l_p' => $strategyLP,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="strategy_l_p_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, StrategyLP $strategyLP): Response
    {
        if ($strategyLP->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('strategy_farming_index');
        }

        $form = $this->createForm(StrategyLPType::class, $strategyLP);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_lp/edit.html.twig', [
            'strategy_l_p' => $strategyLP,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="strategy_l_p_delete", methods={"POST"})
     */
    public function delete(Request $request, StrategyLP $strategyLP): Response
    {
        if ($strategyLP->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('strategy_farming_index');
        }

        if ($this->isCsrfTokenValid('delete' . $strategyLP->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($strategyLP);
            $entityManager->flush();
        }

        return $this->redirectToRoute('strategy_farming_index', [], Response::HTTP_SEE_OTHER);
    }
}
