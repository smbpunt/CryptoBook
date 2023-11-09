<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MenuBuilder
{
    private $factory;

    private Security $security;

    /**
     * Add any other dependency you need...
     */
    public function __construct(FactoryInterface $factory, Security $security)
    {
        $this->factory = $factory;
        $this->security = $security;
    }

    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttribute('class', 'navbar-nav');

        if ($this->security->getUser()) {
            $menu->addChild('Accueil', ['route' => 'home']);
            $menu->addChild('Position', ['route' => 'app_position_index']);
            $menu->addChild('Farming', ['route' => 'app_strategy_farming_index']);
            $menu->addChild('Emprunts', ['route' => 'app_loan_index']);
            $menu->addChild('DCA', ['route' => 'app_strategy_dca_index']);
            $menu->addChild('Dépôts €', ['route' => 'app_deposit_index']);
            $menu->addChild('NFT', ['route' => 'app_nft_index']);
            $menu->addChild('Projets', ['route' => 'app_project_monitoring_index']);
            $menu->addChild('Préférences', ['route' => 'app_user_preferences']);

            if ($this->security->isGranted('ROLE_ADMIN')) {
                $menu->addChild('Admin', ['route' => 'admin']);
            }

            $menu->addChild('Logout', ['route' => 'app_logout']);
        } else {
            $menu->addChild('Login', ['route' => 'app_login']);
            $menu->addChild('Register', ['route' => 'app_register']);

        }


        foreach ($menu as $child) {
            $child->setLinkAttribute('class', 'nav-link')
                ->setAttribute('class', 'nav-item');
        }

        return $menu;
    }
}