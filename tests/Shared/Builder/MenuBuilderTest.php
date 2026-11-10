<?php

namespace App\Tests\Shared\Builder;

use App\Shared\Builder\MenuBuilder;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

final class MenuBuilderTest extends KernelTestCase
{
    public function testMainMenuListsTheBusinessPages(): void
    {
        $this->loginAs(static fn (Dataset $data): UserInterface => $data->user);

        self::assertSame(
            ['Tableau de bord', 'Positions', 'Farming', 'Emprunts', 'DCA', 'Dépôts', 'NFT', 'Projets'],
            self::labels(self::builder()->createMainMenu([])),
        );
    }

    public function testAccountMenuHidesAdminFromRegularUsers(): void
    {
        $this->loginAs(static fn (Dataset $data): UserInterface => $data->user);

        self::assertSame(['Préférences', 'Déconnexion'], self::labels(self::builder()->createAccountMenu([])));
    }

    public function testAccountMenuShowsAdminToAdmins(): void
    {
        $this->loginAs(static fn (Dataset $data): UserInterface => $data->admin);

        self::assertSame(['Préférences', 'Admin', 'Déconnexion'], self::labels(self::builder()->createAccountMenu([])));
    }

    public function testAnonymousUsersOnlyGetTheLoginLinks(): void
    {
        self::bootKernel();

        self::assertSame([], self::labels(self::builder()->createMainMenu([])));
        self::assertSame(['Connexion', 'Inscription'], self::labels(self::builder()->createAccountMenu([])));
    }

    /**
     * @param \Closure(Dataset): UserInterface $user
     */
    private function loginAs(\Closure $user): void
    {
        self::bootKernel();
        $logged = $user(Dataset::load(self::getContainer()->get(EntityManagerInterface::class)));
        self::getContainer()->get(TokenStorageInterface::class)
            ->setToken(new UsernamePasswordToken($logged, 'main', $logged->getRoles()));
    }

    private static function builder(): MenuBuilder
    {
        return self::getContainer()->get(MenuBuilder::class);
    }

    /**
     * @return list<string>
     */
    private static function labels(ItemInterface $menu): array
    {
        return array_values(array_map(static fn (ItemInterface $item): string => $item->getLabel(), $menu->getChildren()));
    }
}
