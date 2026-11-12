<?php

namespace App\Tests\Home\Controller;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class HomeControllerTest extends WebTestCase
{
    public function testLoggedInUserIsRedirectedToTheDashboard(): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->user);

        $client->request('GET', '/');

        self::assertResponseRedirects('/dashboard');
    }

    public function testAnonymousSeesTheHomepage(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        // Two assertions: a <br> splits the title.
        self::assertSelectorTextContains('h1', 'Ce que valent vos cryptos.');
        self::assertSelectorTextContains('h1', "Ce qu'elles vous ont coûté.");
        self::assertPageTitleSame('Suivi de portefeuille crypto · CryptoBook');
        foreach (['header a[href="/login"]', 'header a[href="/register"]', 'main a[href="/register"]', 'footer a[href="/login"]'] as $link) {
            self::assertGreaterThan(0, $crawler->filter($link)->count(), $link);
        }
        self::assertSelectorExists('header a[href="/"] svg[aria-label="CryptoBook"]');
        self::assertSelectorExists('button[data-controller="theme"]');
        self::assertSelectorNotExists('nav[aria-label="Navigation principale"]');
        self::assertSelectorNotExists('button[aria-label="Compte"]');
    }

    public function testEveryAnchorHasItsSection(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/');

        $anchors = $crawler->filter('a[href^="#"]')->each(static fn (Crawler $a): string => (string) $a->attr('href'));
        self::assertNotEmpty($anchors);
        foreach ($anchors as $anchor) {
            self::assertCount(1, $crawler->filter($anchor), "Target of $anchor");
        }
        self::assertSame(
            ['#dashboard', '#positions', '#cryptos', '#deposits'],
            $crawler->filter('header nav a')->each(static fn (Crawler $a): string => (string) $a->attr('href')),
        );
    }

    public function testShowsTheExampleFigures(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/');

        $main = self::normalize($crawler->filter('main')->text());
        foreach (['42 618,40 €', '+3,2 %', '+11 118,40 €', '−11,9 %'] as $figure) {
            self::assertStringContainsString($figure, $main);
        }
    }

    public function testCarriesTheSeoTags(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/');

        self::assertNotEmpty($crawler->filter('meta[name="description"]')->attr('content'));
        self::assertSame('http://localhost/', $crawler->filter('link[rel="canonical"]')->attr('href'));
        self::assertSame('http://localhost/', $crawler->filter('meta[property="og:url"]')->attr('content'));
        self::assertMatchesRegularExpression('#^http://localhost/assets/images/icon-512-[\w-]+\.png$#', (string) $crawler->filter('meta[property="og:image"]')->attr('content'));
        self::assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    /**
     * Narrow and regular no-break spaces as plain spaces, for readable assertions.
     */
    private static function normalize(string $text): string
    {
        return trim(str_replace(["\u{202F}", "\u{00A0}"], ' ', $text));
    }
}
