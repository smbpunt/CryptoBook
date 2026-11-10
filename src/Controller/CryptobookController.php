<?php

namespace App\Controller;

use App\Dashboard\Cache\DashboardCache;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class CryptobookController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(DashboardCache $dashboardCache, #[CurrentUser] User $user): Response
    {
        return $this->render('cryptobook/index.html.twig', [
            'dashboard' => $dashboardCache->get($user),
        ]);
    }
}
