<?php

namespace App\Controller;

use App\Entity\StrategyFarming;
use App\Form\StrategyFarmingType;
use App\Repository\StrategyFarmingRepository;
use App\Repository\StrategyLPRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/strategy/farming")
 */
class StrategyFarmingController extends AbstractController
{
    /**
     * @Route("/", name="strategy_farming_index", methods={"GET"})
     */
    public function index(StrategyFarmingRepository $strategyFarmingRepository, StrategyLPRepository $strategyLPRepository): Response
    {
        if ($this->getUser()) {
            $strategies = $strategyFarmingRepository->findByStable($this->getUser(), false);
            $strategies_stable = $strategyFarmingRepository->findByStable($this->getUser(), true);
            $strategies_lp = $strategyLPRepository->findBy([
                'user' => $this->getUser()
            ]);

            $total_year = 0;
            $total_year_stable = 0;
            $total_year_lp = 0;

            $return_array = [];
            foreach ($strategies as $strategy) {
                $value_dollar = $strategy->getCoin()->getPriceUsd() * $strategy->getNbCoins();
                $gain_year = $strategy->getApr() * $value_dollar / 100;
                $total_year += $gain_year;
                $return_array[] = [
                    'strategy' => $strategy,
                    'gain_year' => $gain_year,
                    'value_dollar' => $value_dollar
                ];
            }

            $return_array_stable = [];
            foreach ($strategies_stable as $strategy) {
                $value_dollar = $strategy->getCoin()->getPriceUsd() * $strategy->getNbCoins();
                $gain_year = $strategy->getApr() * $value_dollar / 100;
                $total_year_stable += $gain_year;
                $return_array_stable[] = [
                    'strategy' => $strategy,
                    'gain_year' => $gain_year,
                    'value_dollar' => $value_dollar
                ];
            }

            $return_array_lp = [];
            foreach ($strategies_lp as $strategy) {
                $value_dollar = $strategy->getCoin1()->getPriceUsd() * $strategy->getNbCoin1() + $strategy->getCoin2()->getPriceUsd() * $strategy->getNbCoin2();
                $gain_year = $strategy->getApr() * $value_dollar / 100;
                $total_year_lp += $gain_year;
                $return_array_lp[] = [
                    'strategy' => $strategy,
                    'gain_year' => $gain_year,
                    'value_dollar' => $value_dollar
                ];
            }

            return $this->render('strategy_farming/index.html.twig', [
                'strategy_farmings' => $return_array,
                'strategy_farmings_stable' => $return_array_stable,
                'strategy_farmings_lp' => $return_array_lp,
                'total_year' => $total_year,
                'total_year_stable' => $total_year_stable,
                'total_year_lp' => $total_year_lp,
                'total_total_year' => $total_year + $total_year_stable + $total_year_lp
            ]);
        }

        return $this->render('cryptobook/index.html.twig');
    }

    /**
     * @Route("/new", name="strategy_farming_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $strategyFarming = new StrategyFarming($this->getUser());
        $form = $this->createForm(StrategyFarmingType::class, $strategyFarming);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($strategyFarming);
            $entityManager->flush();

            return $this->redirectToRoute('strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_farming/new.html.twig', [
            'strategy_farming' => $strategyFarming,
            'form' => $form,
        ]);
    }


    /**
     * @Route("/{id}/infos", name="farming-infos-ajax", methods={"POST"})
     */
    public function ajaxStrategy(Request $request, StrategyFarming $strategyFarming): Response
    {
        return $this->render('strategy_farming/_informations.twig', [
            'farming' => $strategyFarming,
        ]);
    }

    /**
     * @Route("/{id}", name="strategy_farming_show", methods={"GET"})
     */
    public function show(StrategyFarming $strategyFarming): Response
    {
        if ($strategyFarming->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('strategy_farming_index');
        }

        return $this->render('strategy_farming/show.html.twig', [
            'strategy_farming' => $strategyFarming,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="strategy_farming_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, StrategyFarming $strategyFarming): Response
    {
        if ($strategyFarming->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('strategy_farming_index');
        }

        $form = $this->createForm(StrategyFarmingType::class, $strategyFarming);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('strategy_farming_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('strategy_farming/edit.html.twig', [
            'strategy_farming' => $strategyFarming,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="strategy_farming_delete", methods={"POST"})
     */
    public function delete(Request $request, StrategyFarming $strategyFarming): Response
    {
        if ($strategyFarming->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('strategy_farming_index');
        }

        if ($this->isCsrfTokenValid('delete' . $strategyFarming->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($strategyFarming);
            $entityManager->flush();
        }

        return $this->redirectToRoute('strategy_farming_index', [], Response::HTTP_SEE_OTHER);
    }
}
