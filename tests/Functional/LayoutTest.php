<?php

namespace App\Tests\Functional;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
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

    public function testAnonymousPageHasNoHardCodedThemeNorMainMenu(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSame('', (string) $crawler->filter('html')->attr('class'));
        self::assertSelectorExists('meta[name="color-scheme"][content="light dark"]');
        self::assertPageTitleSame('Connexion');
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
}
