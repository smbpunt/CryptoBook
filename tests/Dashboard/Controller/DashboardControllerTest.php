<?php

namespace App\Tests\Dashboard\Controller;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Dashboard\Cookie\DisplayCurrencyCookie;
use App\Position\Entity\Position;
use App\Tests\Fixtures\Dataset;
use App\Wallet\Entity\WalletSnapshot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie as BrowserCookie;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

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

        $crawler = $this->client->request('GET', '/dashboard');

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

        self::assertSame(['EUR', 'USD'], self::texts($crawler, '[aria-label="Devise d’affichage"] button'));
        self::assertSame('EUR', self::text($crawler, '[aria-label="Devise d’affichage"] [aria-pressed="true"]'));
        self::assertNull($this->currencyCookie());
    }

    public function testGroupsTheSmallestHoldingsInOthers(): void
    {
        // 25 000 $ of BTC + 8 coins at 1 $: 27 745 $. C5 to C8 are below 0.3 %: 6 slices (5 + "Autres") and
        // 1 stablecoin, so 6 rows (counting the 9 holdings instead of the slices would give 7).
        foreach ([800, 700, 600, 500, 50, 40, 30, 25] as $i => $quantity) {
            $coin = new Cryptocurrency()->setLibelleCoingecko('coin-'.($i + 1))->setLibelle('Coin '.($i + 1))
                ->setSymbol('c'.($i + 1))->setPriceUsd(1)->setMcapUsd(1000)->setIsStable(false);
            $this->em->persist($coin);
            $this->em->persist(new Position($this->data->user)->setCoin($coin)->setNbCoins($quantity)->setEntryCost($quantity));
        }
        $this->em->flush();
        $this->client->loginUser($this->data->user);

        $crawler = $this->client->request('GET', '/dashboard');

        self::assertResponseIsSuccessful();
        $rows = '[aria-labelledby="dashboard-holdings"] tbody tr';
        self::assertSame(['BTC', 'C1', 'C2', 'C3', 'C4', 'Autres'], self::texts($crawler, $rows.' td:first-child .font-semibold'));
        // C5 to C8 = 145 $ = 135 €, 0.52 % of the crypto wallet (0.50 % of the total with the stablecoin).
        $others = $rows.'[data-slot="holdings-others"]';
        self::assertSame(['Autres4actifs', '—', '—', '135 €', '0,5 %'], self::texts($crawler, $others.' td', true));
        self::assertSame('width: 0.52%; background: var(--muted-foreground)', $crawler->filter($others.' td:last-child [style]')->attr('style'));
        self::assertStringNotContainsString('C5', self::text($crawler, '[aria-labelledby="dashboard-holdings"]'));

        // Coins without a color: the one derived from the symbol, on the weight bar and the stablecoin dot.
        self::assertSame('width: 90.11%; background: hsl(68 65% 55%)', $crawler->filter($rows.':first-child td:last-child [style]')->attr('style'));
        self::assertSame('background: hsl(234 65% 55%)', $crawler->filter('[aria-labelledby="dashboard-allocation"] h3 + ul [style]')->attr('style'));
    }

    public function testShowsTheDashboardInUsd(): void
    {
        $this->snapshot('2026-11-09 12:00', 24000);
        $this->snapshot('2026-11-10 14:00', 30000);
        $this->client->loginUser($this->data->user);
        $this->displayInUsd();

        $crawler = $this->client->request('GET', '/dashboard');

        self::assertResponseIsSuccessful();
        self::assertSame('26 000 $', self::text($crawler, '#wallet-history-title + p'));
        self::assertSame(['+8,3 %', '+2 000 $', 'depuis le 9 nov.'], self::texts($crawler, '#wallet-history-title ~ div > *'));

        self::assertSame('+24 926 $', self::text($crawler, '[aria-labelledby="dashboard-result"] p'));
        self::assertSame('+2 000 $', self::text($crawler, '[aria-labelledby="dashboard-day-change"] p'));
        self::assertSame('30 000 $', self::text($crawler, '[aria-labelledby="dashboard-high"] p'));

        $invested = '//*[@data-slot="card-description"][normalize-space()="Investi"]/following-sibling::*';
        self::assertSame('1 074 $', self::normalize($crawler->filterXPath($invested.'[@data-slot="card-title"]')->text()));
        self::assertStringNotContainsString('·', $crawler->filterXPath($invested.'[self::p]')->text());
        self::assertStringContainsString('50 $ / mois', self::text($crawler, 'main'));

        self::assertSame(['USDC', '1 000 $', '100,0 %'], self::texts($crawler, '[aria-labelledby="dashboard-allocation"] h3 + ul li > span:not([aria-hidden])'));
        self::assertSame(['BTCBitcoin', '0,5', '50 000 $', '25 000 $', '100,0 %'], self::texts($crawler, '[aria-labelledby="dashboard-holdings"] tbody td', true));

        // The chart in dollars: the first snapshot is 24 000 $ (22 347,6 € in euros).
        $canvas = $crawler->filter('canvas[data-wallet-chart-currency-value="$"]');
        self::assertCount(1, $canvas);
        /** @var array{data: array{datasets: list<array{data: list<array{y: float}>}>}} $chart */
        $chart = json_decode((string) $canvas->attr('data-symfony--ux-chartjs--chart-view-value'), true, flags: \JSON_THROW_ON_ERROR);
        self::assertEqualsWithDelta(24000.0, $chart['data']['datasets'][0]['data'][0]['y'], 0.001);

        self::assertSame('USD', self::text($crawler, '[aria-label="Devise d’affichage"] [aria-pressed="true"]'));
        $this->assertCurrencyCookieExtended();
    }

    public function testChangeOfPeriodInUsdExtendsTheCookie(): void
    {
        $this->snapshot('2026-11-10 10:00', 24000);
        $this->client->loginUser($this->data->user);
        $this->displayInUsd();

        $crawler = $this->client->request('GET', '/dashboard/history/7d', server: ['HTTP_TURBO_FRAME' => 'wallet-history']);

        self::assertResponseIsSuccessful();
        self::assertSame('26 000 $', self::text($crawler, '#wallet-history-title + p'));
        self::assertSelectorExists('canvas[data-wallet-chart-currency-value="$"]');
        $this->assertCurrencyCookieExtended();
    }

    public function testNoSwitchWhenTheFavoriteCurrencyIsUsd(): void
    {
        $this->client->loginUser($this->data->admin);
        $this->displayInUsd();

        $this->client->request('GET', '/dashboard');

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('[aria-label="Devise d’affichage"]');
        self::assertNull($this->currencyCookie());
    }

    public function testSwitchToUsd(): void
    {
        $this->client->loginUser($this->data->user);
        $crawler = $this->client->request('GET', '/dashboard');

        $this->client->submit($crawler->selectButton('USD')->form());

        self::assertResponseRedirects('/dashboard', Response::HTTP_SEE_OTHER);
        $this->assertCurrencyCookieExtended();
        self::assertTrue($this->currencyCookie()?->isHttpOnly());
    }

    public function testSwitchBackToTheFavoriteCurrency(): void
    {
        $this->client->loginUser($this->data->user);
        $this->displayInUsd();
        $crawler = $this->client->request('GET', '/dashboard');

        $this->client->submit($crawler->selectButton('EUR')->form());

        self::assertResponseRedirects('/dashboard', Response::HTTP_SEE_OTHER);
        self::assertTrue($this->currencyCookie()?->isCleared());
        self::assertSame('/', $this->currencyCookie()->getPath());
    }

    public function testUnknownCurrencyIsNotFound(): void
    {
        $this->client->loginUser($this->data->user);

        $this->client->request('POST', '/dashboard/currency/btc');

        self::assertResponseStatusCodeSame(404);
    }

    public function testEmptyHistory(): void
    {
        $this->client->loginUser($this->data->admin);

        $crawler = $this->client->request('GET', '/dashboard');

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

        $crawler = $this->client->request('GET', '/dashboard/history/7d', server: ['HTTP_TURBO_FRAME' => 'wallet-history']);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('body > turbo-frame#wallet-history'));
        self::assertSelectorNotExists('header');
        self::assertSame('7 j', self::text($crawler, '[aria-current="true"]'));
        self::assertSelectorExists('canvas[data-wallet-chart-hourly-value="true"]');
    }

    public function testDirectVisitOfAPeriodGoesToTheDashboard(): void
    {
        $this->client->loginUser($this->data->user);

        $this->client->request('GET', '/dashboard/history/7d');

        self::assertResponseRedirects('/dashboard');
    }

    public function testUnknownPeriodIsNotFound(): void
    {
        $this->client->loginUser($this->data->user);

        $this->client->request('GET', '/dashboard/history/2w');

        self::assertResponseStatusCodeSame(404);
    }

    /**
     * Set in the jar directly: the cookie of a response expires on the mocked clock, the jar on the real one.
     */
    private function displayInUsd(): void
    {
        $this->client->getCookieJar()->set(new BrowserCookie(DisplayCurrencyCookie::NAME, 'usd'));
    }

    private function currencyCookie(): ?Cookie
    {
        foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
            if (DisplayCurrencyCookie::NAME === $cookie->getName()) {
                return $cookie;
            }
        }

        return null;
    }

    private function assertCurrencyCookieExtended(): void
    {
        $cookie = $this->currencyCookie();
        self::assertSame('usd', $cookie?->getValue());
        self::assertSame(new \DateTimeImmutable('2026-12-10 14:20 UTC')->getTimestamp(), $cookie->getExpiresTime());
        // Path "/": unchanged since the dashboard moved to /dashboard, an existing cookie must stay clearable.
        self::assertSame('/', $cookie->getPath());
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
