<?php

namespace App\Tests\Dashboard\Controller;

use App\Tests\Fixtures\Dataset;
use App\Wallet\Entity\WalletSnapshot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\DomCrawler\Crawler;

final class DashboardControllerTest extends WebTestCase
{
    use ClockSensitiveTrait;

    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private Dataset $data;

    protected function setUp(): void
    {
        self::mockTime('2026-11-10 14:20 UTC');
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->data = Dataset::load($this->em);
    }

    public function testShowsTheBlocksOfTheDashboard(): void
    {
        // 1 EUR = 1.073941 USD (migration rate): the user sees euros, 26 000 $ = 24 210 €.
        $this->snapshot('2026-11-09 12:00', 24000);
        $this->snapshot('2026-11-10 14:00', 30000);
        $this->client->loginUser($this->data->user);

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Dernier snapshot · 10 nov. 2026');
        self::assertSame('24 210 €', self::text($crawler, '#wallet-history-title + p'));
        // 30 days: from the first snapshot (24 000 $) to the current total (26 000 $).
        self::assertSame(['+8,3 %', '+1 862 €', 'depuis le 9 nov. · soit 26 000 $'], self::texts($crawler, '#wallet-history-title ~ div > *'));
        self::assertSelectorExists('turbo-frame#wallet-history canvas[role="img"]');
        self::assertSame('30 j', self::text($crawler, 'nav[aria-label="Période du graphique"] [aria-current="true"]'));

        self::assertSame(['Résultat latent', '+2 321,0 %', '+23 210 €'], self::texts($crawler, '[aria-labelledby="dashboard-result"] :is(h2, [data-slot="delta"], p)'));
        // 24 hours ago: the last snapshot between -48 h and -24 h (24 000 $).
        self::assertSame(['Variation 24 h', '+1 862 €', '+8,3 %'], self::texts($crawler, '[aria-labelledby="dashboard-day-change"] :is(h2, p, [data-slot="delta"])'));
        self::assertSame(['Plus haut historique · 10 nov. 2026', '27 934 €', '−13,3 %'], self::texts($crawler, '[aria-labelledby="dashboard-high"] :is(h2, p, [data-slot="delta"])'));

        $main = self::text($crawler, 'main');
        self::assertSame(['Investi', 'Revenus farming', 'Emprunts', 'Crypto / stable'], self::texts($crawler, '[data-slot="card-description"]'));
        self::assertSame('1 000 €', self::normalize($crawler->filterXPath('//*[@data-slot="card-description"][normalize-space()="Investi"]/following-sibling::*[@data-slot="card-title"]')->text()));
        // 2 ETH at 5 % + LP of 4 000 $ at 10 % = 600 $ a year, 559 € = 47 € a month.
        self::assertStringContainsString('47 € / mois', $main);
        self::assertStringContainsString('1,9 % de la valeur du wallet', $main);
        self::assertStringContainsString('96 % / 4 %', $main);

        self::assertSame(['BTC', '100,0 %'], self::texts($crawler, '#dashboard-allocation + div li > span:not([aria-hidden])'));
        self::assertSame(['USDC', '931 €', '100,0 %'], self::texts($crawler, '[aria-labelledby="dashboard-allocation"] h3 + ul li > span:not([aria-hidden])'));
        self::assertSame(['BTCBitcoin', '0,5', '46 557 €', '23 279 €', '100,0 %'], self::texts($crawler, '[aria-labelledby="dashboard-holdings"] tbody td', true));
    }

    public function testEmptyHistory(): void
    {
        $this->client->loginUser($this->data->admin);

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Aucun snapshot pour l’instant');
        self::assertSelectorExists('turbo-frame#wallet-history [data-slot="empty"]');
        self::assertSelectorNotExists('canvas');
        self::assertSame('—', self::text($crawler, '[aria-labelledby="dashboard-day-change"] p'));
        self::assertSame('—', self::text($crawler, '[aria-labelledby="dashboard-high"] p'));
        self::assertSelectorTextContains('[aria-labelledby="dashboard-allocation"]', 'Aucune crypto');
        self::assertSelectorExists('[aria-labelledby="dashboard-holdings"] [data-slot="empty"]');
    }

    public function testChangeOfPeriodRendersTheFrameOnly(): void
    {
        $this->snapshot('2026-11-10 10:00', 24000);
        $this->client->loginUser($this->data->user);

        $crawler = $this->client->request('GET', '/history/7d', server: ['HTTP_TURBO_FRAME' => 'wallet-history']);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('body > turbo-frame#wallet-history'));
        self::assertSelectorNotExists('header');
        self::assertSame('7 j', self::text($crawler, '[aria-current="true"]'));
        self::assertSelectorExists('canvas[data-wallet-chart-hourly-value="true"]');
    }

    public function testDirectVisitOfAPeriodGoesToTheDashboard(): void
    {
        $this->client->loginUser($this->data->user);

        $this->client->request('GET', '/history/7d');

        self::assertResponseRedirects('/');
    }

    public function testUnknownPeriodIsNotFound(): void
    {
        $this->client->loginUser($this->data->user);

        $this->client->request('GET', '/history/2w');

        self::assertResponseStatusCodeSame(404);
    }

    private function snapshot(string $at, float $valueUsd): void
    {
        $this->em->persist(new WalletSnapshot($this->data->user, new \DateTimeImmutable($at.' UTC'), $valueUsd));
        $this->em->flush();
    }

    private static function text(Crawler $crawler, string $selector): string
    {
        return self::normalize($crawler->filter($selector)->text());
    }

    /**
     * @return list<string>
     */
    private static function texts(Crawler $crawler, string $selector, bool $compact = false): array
    {
        return $crawler->filter($selector)->each(static fn (Crawler $node): string => self::normalize($compact ? str_replace(' ', '', $node->text()) : $node->text()));
    }

    /**
     * Narrow and regular no-break spaces as plain spaces, for readable assertions.
     */
    private static function normalize(string $text): string
    {
        return trim(str_replace(["\u{202F}", "\u{00A0}"], ' ', $text));
    }
}
