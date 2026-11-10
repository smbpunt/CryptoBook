<?php

namespace App\Tests\Functional;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * With Turbo, page scripts are Stimulus controllers: each page carries the ones it needs, and loads the `app` entrypoint only.
 */
final class StimulusControllersTest extends WebTestCase
{
    public function testPagesCarryTheirControllers(): void
    {
        $client = self::createClient();
        $d = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($d->user);

        $pages = [
            '/' => ['[data-controller="allocation-chart"]'],
            '/position/' => ['table[data-controller="row-toggle"] button[data-action="row-toggle#toggle"][aria-controls^="detail-"]'],
            // The form theme adds `choices` to every `js-select2` select: checked once here.
            '/position/new' => ['select[data-controller~="choices"]', '[data-controller="collection"] [data-collection-target="items"]', 'button[data-action="collection#add"]'],
            "/position/{$d->position->getId()}/edit" => ['[data-controller="collection"]'],
            // `choices` and `dependent-select` on the same select.
            '/loan/new' => ['form[data-controller="dependent-select"] select[data-action="dependent-select#refresh"][data-controller~="choices"]', '#id-dapp[data-dependent-select-target="field"]'],
            '/strategy/farming/new' => ['form[data-controller="dependent-select"] #strategy_farming_blockchain[data-action="dependent-select#refresh"][data-controller~="choices"]', '#id-dapp[data-dependent-select-target="field"]', '#strategy_farming_coin[data-controller~="choices"]'],
            '/strategy/lp/new' => ['form[data-controller="dependent-select"]', '#id-dapp[data-dependent-select-target="field"]'],
            '/project/monitoring/new' => ['[data-controller="collection"]'],
            '/strategy/dca/' => ['[data-controller="dca"] button[data-action="dca#generate"]', 'form[data-dca-target="autoForm"] button[data-action="dca#auto"]'],
            '/strategy/dca/edit' => ['[data-controller="collection"] button[data-action="collection#remove"]'],
        ];

        $controllersDir = self::getContainer()->getParameter('kernel.project_dir').'/assets/controllers';
        foreach ($pages as $url => $selectors) {
            $crawler = $client->request('GET', $url);
            self::assertResponseIsSuccessful("GET $url");
            foreach ($selectors as $selector) {
                self::assertGreaterThan(0, $crawler->filter($selector)->count(), "$url: $selector");
            }

            // Stimulus silently ignores an identifier without a controller: each one needs its file.
            $identifiers = $crawler->filter('[data-controller]')->each(static fn (Crawler $node): array => preg_split('/\s+/', trim((string) $node->attr('data-controller'))) ?: []);
            foreach (array_unique(array_merge(...$identifiers)) as $identifier) {
                if (str_contains($identifier, '--')) {
                    continue; // Controller of a UX package (`symfony--ux-…`), loaded from controllers.json.
                }
                self::assertFileExists("$controllersDir/".str_replace('-', '_', $identifier).'_controller.js', "$url: $identifier");
            }

            // A single entrypoint: with Turbo, the importmap cannot change from one page to the next.
            $imports = array_filter(
                $crawler->filter('script[type="module"]')->each(static fn (Crawler $script): string => trim($script->text())),
                static fn (string $code): bool => str_starts_with($code, 'import'),
            );
            self::assertSame(["import 'app';"], array_values($imports), "$url: entrypoints");
        }
    }

    public function testAdminAndLogoutLinksLeaveTurbo(): void
    {
        $client = self::createClient();
        $d = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($d->admin);

        $client->request('GET', '/position/');

        self::assertSelectorExists('[data-slot="popover-content"] a[href="/admin"][data-turbo="false"]');
        // Turbo would prefetch the GET logout on hover.
        self::assertSelectorExists('[data-slot="popover-content"] a[href="/logout"][data-turbo="false"]');
    }

    public function testCollectionIndexFollowsTheHighestRowAfterAnInvalidSubmit(): void
    {
        $client = self::createClient();
        $d = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($d->user);

        $crawler = $client->request('GET', '/position/new');
        $values = $crawler->selectButton('Valider')->form()->getPhpValues();
        $values['position'] = ['coin' => (string) $d->eth->getId(), 'nbCoins' => '1', 'entryCost' => '1000'] + $values['position'];
        // Row 1 removed in the browser: keys 0 and 2 come back, a new row must not reuse 2.
        $values['position']['ventes'] = [0 => ['soldAt' => '2024-02-01', 'percent' => '150', 'priceSold' => '3000'], 2 => ['soldAt' => '2024-03-01', 'percent' => '10', 'priceSold' => '3000']];

        $crawler = $client->request('POST', '/position/new', $values);

        self::assertResponseStatusCodeSame(422);
        self::assertSame('3', $crawler->filter('#tab-vente-description [data-controller="collection"]')->attr('data-collection-index-value'));
    }
}
