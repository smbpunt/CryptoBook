<?php

namespace App\Controller;

use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class CryptobookController extends AbstractController
{
    /**
     * @Route("", name="home")
     */
    public function index(PositionRepository $positionRepository): Response
    {
        $positions = $positionRepository->getSumCoinByUser($this->getUser());
        foreach ($positions as $key => $value) {
            $value['valueUsd'] = $value['totalsum'] * $value['priceUsd'];
            $value['valueEur'] = $value['totalsum'] * $value['priceEur'];
            $positions[$key] = $value;
        }

        dump($positions);
        return $this->render('cryptobook/index.html.twig', [
            'positions' => $positions
        ]);
    }
}
