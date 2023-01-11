<?php

namespace App\Controller\Admin;

use App\Entity\Blockchain;
use App\Entity\Cryptocurrency;
use App\Entity\Dapp;
use App\Entity\Exchange;
use App\Entity\FiatCurrency;
use App\Entity\TypeProject;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(CryptocurrencyCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('CryptoBook');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Retour', 'fa fa-chevron-circle-left', 'home');
        yield MenuItem::linktoCrud('Cryptocurrencies', 'fab fa-bitcoin', Cryptocurrency::class);
        yield MenuItem::linktoCrud('Utilisateurs', 'fas fa-user', User::class);
        yield MenuItem::linktoCrud('Exchange', 'fa fa-university', Exchange::class);
        yield MenuItem::linktoCrud('Blockchain', 'fa fa-link', Blockchain::class);
        yield MenuItem::linktoCrud('Dapps', 'fa fa-cloud', Dapp::class);
        yield MenuItem::linktoCrud('Type de projet', 'fa fa-file-text', TypeProject::class);
        yield MenuItem::linktoCrud('FIAT Currencies', 'fa fa-dollar-sign ', FiatCurrency::class);
    }
}
