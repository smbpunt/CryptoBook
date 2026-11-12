<?php

namespace App\Admin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(CryptocurrencyCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('CryptoBook')
            ->setFaviconPath('images/favicon.svg');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Retour', 'fa fa-chevron-circle-left', 'home');
        yield MenuItem::linkTo(CryptocurrencyCrudController::class, 'Cryptocurrencies', 'fab fa-bitcoin');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fas fa-user');
        yield MenuItem::linkTo(ExchangeCrudController::class, 'Exchange', 'fa fa-university');
        yield MenuItem::linkTo(BlockchainCrudController::class, 'Blockchain', 'fa fa-link');
        yield MenuItem::linkTo(DappCrudController::class, 'Dapps', 'fa fa-cloud');
        yield MenuItem::linkTo(TypeProjectCrudController::class, 'Type de projet', 'fa fa-file-text');
        yield MenuItem::linkTo(FiatCurrencyCrudController::class, 'FIAT Currencies', 'fa fa-dollar-sign');
    }
}
