<?php

namespace App\Tests\Functional;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Header shared by every page: logo, main navigation, theme switch and account menu.
 */
final class LayoutTest extends WebTestCase
{
    public function testHeaderForALoggedInUser(): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->user);

        $crawler = $client->request('GET', '/position/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('nav[aria-label="Navigation principale"] [aria-current="page"]', 'Positions');
        self::assertSelectorExists('button[data-controller="theme"]');
        self::assertSelectorExists('button[aria-label="Compte"][aria-haspopup="dialog"]');
        self::assertSame(
            ['Préférences', 'Déconnexion'],
            $crawler->filter('[data-slot="popover-content"] li')->each(static fn ($li): string => trim($li->text())),
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function subPages(): iterable
    {
        yield 'dashboard' => ['/dashboard', 'Tableau de bord'];
        yield 'position form' => ['/position/new', 'Positions'];
        yield 'LP form, under farming' => ['/strategy/lp/new', 'Farming'];
    }

    #[DataProvider('subPages')]
    public function testSubPageKeepsItsTabCurrent(string $url, string $tab): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->user);

        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('nav[aria-label="Navigation principale"] [aria-current="page"]', $tab);
    }

    public function testAnonymousPageHasNoHardCodedThemeNorMainMenu(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSame('', (string) $crawler->filter('html')->attr('class'));
        self::assertSelectorExists('meta[name="color-scheme"][content="light dark"]');
        self::assertPageTitleSame('Connexion · CryptoBook');
        self::assertSelectorExists('svg[role="img"][aria-label="CryptoBook"]');
        self::assertSelectorNotExists('nav[aria-label="Navigation principale"]');
    }

    public function testErrorPageKeepsTheThemeSwitchOnly(): void
    {
        $client = self::createClient(['debug' => false]);

        $client->request('GET', '/does-not-exist');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorExists('button[data-controller="theme"]');
        self::assertSelectorNotExists('nav[aria-label="Navigation principale"]');
        self::assertSelectorNotExists('button[aria-label="Compte"]');
    }

    public function testEveryPageDeclaresTheFavicons(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/login');

        // A missing file falls back to "/images/…", without the AssetMapper prefix and digest.
        self::assertMatchesRegularExpression('#^/assets/images/favicon-[\w-]+\.svg$#', (string) $crawler->filter('link[rel="icon"][type="image/svg+xml"]')->attr('href'));
        self::assertMatchesRegularExpression('#^/assets/images/favicon-32-[\w-]+\.png$#', (string) $crawler->filter('link[rel="icon"][sizes="32x32"]')->attr('href'));
        self::assertMatchesRegularExpression('#^/assets/images/apple-touch-icon-[\w-]+\.png$#', (string) $crawler->filter('link[rel="apple-touch-icon"]')->attr('href'));
    }

    public function testAdminKeepsTheFaviconAndLinksBackToTheDashboard(): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->admin);

        $crawler = $client->request('GET', '/admin/cryptocurrency');

        self::assertResponseIsSuccessful();
        self::assertMatchesRegularExpression('#^/assets/images/favicon-[\w-]+\.svg$#', (string) $crawler->filter('link[rel="shortcut icon"]')->attr('href'));
        // EasyAdmin routes the link through itself: "/admin?routeName=…".
        self::assertStringContainsString('routeName=app_dashboard_index', (string) $crawler->selectLink('Retour')->attr('href'));
    }
}
