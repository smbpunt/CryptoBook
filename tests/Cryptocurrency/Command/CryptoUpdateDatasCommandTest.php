<?php

namespace App\Tests\Cryptocurrency\Command;

use App\Cryptocurrency\Repository\CryptocurrencyRepository;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CryptoUpdateDatasCommandTest extends KernelTestCase
{
    public function testUpdatesEveryCoinFromCoinGecko(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $data = Dataset::load($em);

        $paths = [];
        $http = self::getContainer()->get('http_client.transport');
        \assert($http instanceof MockHttpClient);
        $http->setResponseFactory(static function (string $method, string $url) use (&$paths): MockResponse {
            self::assertStringStartsWith('https://api.coingecko.com/api/v3/coins/', $url);
            $path = (string) parse_url($url, \PHP_URL_PATH);
            $id = basename($path);
            $paths[] = $path;

            // ethereum fails: it must keep its data and not stop the other coins.
            if ('ethereum' === $id) {
                return new MockResponse('', ['http_code' => 500]);
            }

            return new JsonMockResponse([
                'name' => ucfirst($id),
                'symbol' => $id,
                'image' => ['thumb' => "https://img/$id/thumb.png", 'small' => "https://img/$id/small.png", 'large' => "https://img/$id/large.png"],
                'market_data' => ['current_price' => ['usd' => 42], 'market_cap' => ['usd' => 1000]],
            ]);
        });

        $result = self::runCommand('crypto:updateDatas');

        self::assertCommandIsSuccessful($result);
        self::assertEqualsCanonicalizing(
            ['/api/v3/coins/bitcoin', '/api/v3/coins/ethereum', '/api/v3/coins/usd-coin'],
            $paths,
        );

        $em->clear();
        $repository = self::getContainer()->get(CryptocurrencyRepository::class);
        $btc = $repository->find($data->btc->getId());
        self::assertSame('https://img/bitcoin/thumb.png', $btc->getUrlImgThumb());
        self::assertSame('https://img/bitcoin/large.png', $btc->getUrlImgLarge());
        self::assertSame(42.0, $btc->getPriceUsd());
        self::assertSame(1000.0, $btc->getMcapUsd());
        self::assertSame('https://img/usd-coin/small.png', $repository->find($data->usdc->getId())->getUrlImgSmall());

        $eth = $repository->find($data->eth->getId());
        self::assertNull($eth->getUrlImgThumb());
        self::assertSame(2000.0, $eth->getPriceUsd());
    }
}
