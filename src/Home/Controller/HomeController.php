<?php

namespace App\Home\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    /**
     * Public showcase with hard-coded example figures. Logged in (session or remember me): straight to the dashboard.
     */
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard_index');
        }

        return $this->render('home/index.html.twig');
    }
}
