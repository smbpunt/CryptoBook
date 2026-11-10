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
        $usdByLabel = [];
        $crawler->filterXPath("//*[normalize-space()='Bilan']/ancestor::*[.//table][1]//tr")->each(static function (Crawler $row) use (&$usdByLabel): void {
            $cells = $row->filter('td');
            if ($cells->count() >= 2) {
                $usdByLabel[trim($cells->eq(0)->text())] = trim($cells->eq(1)->text());
            }
        });

        self::assertSame('25 000 $', $usdByLabel['Wallet crypto']);
        self::assertSame('1 000 $', $usdByLabel['Wallet stable']);
        self::assertSame('26 000 $', $usdByLabel['Total']);

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
}
