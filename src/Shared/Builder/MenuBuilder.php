<?php

namespace App\Shared\Builder;

use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MenuBuilder
{
    public function __construct(private readonly FactoryInterface $factory, private readonly Security $security)
    {
    }

    /**
     * Business pages, rendered as pills in the header. Empty for anonymous users.
     *
     * @param array<string, mixed> $options
     */
    #[AsMenuBuilder(name: 'main')]
    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if (!$this->security->getUser()) {
            return $menu;
        }

        $menu->addChild('Tableau de bord', ['route' => 'home']);
        $menu->addChild('Positions', ['route' => 'app_position_index']);
        $menu->addChild('Farming', ['route' => 'app_strategy_farming_index']);
        $menu->addChild('Emprunts', ['route' => 'app_loan_index']);
        $menu->addChild('DCA', ['route' => 'app_strategy_dca_index']);
        $menu->addChild('Dépôts', ['route' => 'app_deposit_index']);
        $menu->addChild('NFT', ['route' => 'app_nft_index']);
        $menu->addChild('Projets', ['route' => 'app_project_monitoring_index']);

        return $menu;
    }

    /**
     * Account entries, rendered in the popover behind the account button.
     *
     * @param array<string, mixed> $options
     */
    #[AsMenuBuilder(name: 'account')]
    public function createAccountMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if (!$this->security->getUser()) {
            $menu->addChild('Connexion', ['route' => 'app_login', 'extras' => ['icon' => 'lucide:log-in']]);
            $menu->addChild('Inscription', ['route' => 'app_register', 'extras' => ['icon' => 'lucide:user-plus']]);

            return $menu;
        }

        $menu->addChild('Préférences', ['route' => 'app_user_preferences', 'extras' => ['icon' => 'lucide:settings']]);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menu->addChild('Admin', ['route' => 'admin', 'extras' => ['icon' => 'lucide:shield']]);
        }

        $menu->addChild('Déconnexion', ['route' => 'app_logout', 'extras' => ['icon' => 'lucide:log-out']]);

        return $menu;
    }
}
