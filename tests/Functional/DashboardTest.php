<?php

namespace App\Tests\Functional;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class DashboardTest extends WebTestCase
{
    public function testShowsTheWalletTotals(): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->user);

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        // Migration rates: 1 EUR = 1.073941 USD, 1 USD = 0.930795 EUR. 1 000 € deposited = 1 073.94 $.
        self::assertSame([
            'Investi' => ['1 074 $', '1 000 €'],
            'Wallet crypto' => ['25 000 $', '23 270 €'],
            'Wallet stable' => ['1 000 $', '931 €'],
            'Total' => ['26 000 $', '24 201 €'],
            'Résultat' => ['24 926 $', '23 201 €'],
            'Résultat %' => ['2 321 %'],
            'Total emprunt' => ['500 $', '465 €'],
        ], self::rows($crawler, 'Bilan'));
        // 2 ETH at 5 % + LP of 4 000 $ at 10 % = 600 $ a year.
        self::assertSame([
            'Jour' => ['2 $', '2 €'],
            'Semaine' => ['12 $', '11 €'],
            'Mois' => ['50 $', '47 €'],
            'Année' => ['600 $', '558 €'],
        ], self::rows($crawler, 'Farming'));
        self::assertSelectorTextContains('table thead', '1.0739 $');

        $pie = json_decode((string) $crawler->filter('#id-array-positions')->attr('data-pie'), true);
        self::assertSame([['libelle' => 'Bitcoin', 'color' => null, 'percent' => 100]], $pie);
    }

    public function testEmptyWalletRenders(): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->admin);

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        $data = $crawler->filter('#id-array-positions');
        self::assertSame('[]', $data->attr('data-pie'));
        self::assertSame('0', $data->attr('data-crypto'));
        self::assertSelectorNotExists('#chart-pos');
    }

    /**
     * @return array<string, list<string>> cells of each row of the card, keyed by the label
     */
    private static function rows(Crawler $crawler, string $card): array
    {
        $rows = [];
        $crawler->filterXPath("//*[@data-slot='card-title'][normalize-space()='{$card}']/ancestor::*[.//table][1]//tbody/tr")->each(static function (Crawler $row) use (&$rows): void {
            $cells = $row->filter('td')->each(static fn (Crawler $cell): string => trim($cell->text()));
            $rows[array_shift($cells)] = $cells;
        });

        return $rows;
    }
}
