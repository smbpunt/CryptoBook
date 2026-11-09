<?php

namespace App\Tests\Functional;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Coin logos come from CoinGecko URLs that may still be empty.
 */
final class CoinLogoTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function pages(): iterable
    {
        yield 'home' => ['/'];
        yield 'positions' => ['/position/'];
        yield 'farming' => ['/strategy/farming/'];
    }

    #[DataProvider('pages')]
    public function testRendersFallbackIconWithoutUrl(string $url): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->user);

        $crawler = $client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('img[alt^="Logo"]'));
        self::assertGreaterThan(0, $crawler->filter('svg[data-slot="coin-fallback"]')->count());
    }

    public function testRendersLogoWithUrl(): void
    {
        $client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $data = Dataset::load($em);
        $data->btc->setUrlImgThumb('https://img/bitcoin/thumb.png');
        $em->flush();
        $client->loginUser($data->user);

        $crawler = $client->request('GET', '/position/');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('img[src="https://img/bitcoin/thumb.png"][alt="Logo Bitcoin"]')->count());
    }
}
