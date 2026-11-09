<?php

namespace App\Tests\Functional;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The dashboard hands assets/home.js the data its charts read.
 */
final class DashboardTest extends WebTestCase
{
    public function testChartsDataMatchesTheWallet(): void
    {
        $client = self::createClient();
        $d = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($d->user);

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        $charts = $crawler->filter('#id-array-positions');
        // 0.5 BTC at 50 000 $ and 1 000 USDC at 1 $.
        self::assertSame('25000', $charts->attr('data-crypto'));
        self::assertSame('1000', $charts->attr('data-stable'));

        $pie = json_decode((string) $charts->attr('data-pie'), true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame([['libelle' => 'Bitcoin', 'color' => null, 'percent' => 100]], array_map(
            static fn (array $holding): array => ['libelle' => $holding['libelle'], 'color' => $holding['color'], 'percent' => $holding['percent']],
            $pie,
        ));
        $stablePie = json_decode((string) $charts->attr('data-piestable'), true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame('USD Coin', $stablePie[0]['libelle']);
    }
}
