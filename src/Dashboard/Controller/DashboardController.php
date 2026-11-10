<?php

namespace App\Dashboard\Controller;

use App\Dashboard\Cache\DashboardCache;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(DashboardCache $dashboardCache, #[CurrentUser] User $user): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'dashboard' => $dashboardCache->get($user),
        ]);
    }
}
