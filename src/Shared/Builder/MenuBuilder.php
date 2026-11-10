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
     * Business pages, rendered as tabs in the header. Empty for anonymous users.
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

        // A tab stays current on its sub-pages (new, edit…): its routes share a prefix.
        $menu->addChild('Tableau de bord', ['route' => 'home']);
        $menu->addChild('Positions', self::tab('app_position_index', '/^app_position_/'));
        $menu->addChild('Farming', self::tab('app_strategy_farming_index', '/^app_strategy_(farming|lp)_/'));
        $menu->addChild('Emprunts', self::tab('app_loan_index', '/^app_loan_/'));
        $menu->addChild('DCA', self::tab('app_strategy_dca_index', '/^app_strategy_dca_/'));
        $menu->addChild('Dépôts', self::tab('app_deposit_index', '/^app_deposit_/'));
        $menu->addChild('NFT', self::tab('app_nft_index', '/^app_nft_/'));
        $menu->addChild('Projets', self::tab('app_project_monitoring_index', '/^app_project_monitoring_/'));

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

    /**
     * @return array{route: string, extras: array{routes: list<array{pattern: string}>}}
     */
    private static function tab(string $route, string $pattern): array
    {
        return ['route' => $route, 'extras' => ['routes' => [['pattern' => $pattern]]]];
    }
}
