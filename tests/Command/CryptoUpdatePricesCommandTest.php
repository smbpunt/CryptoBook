<?php

namespace App\Tests\Command;

use App\Repository\CryptocurrencyRepository;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

final class CryptoUpdatePricesCommandTest extends KernelTestCase
{
    public function testUpdatesPricesFromCoinGecko(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $data = Dataset::load($em);

        // ethereum is missing from the answer: its price must stay untouched.
        $response = new JsonMockResponse([
            'bitcoin' => ['usd' => 65000],
            'usd-coin' => ['usd' => 0.999],
        ]);
        $http = self::getContainer()->get('http_client.transport');
        \assert($http instanceof MockHttpClient);
        $http->setResponseFactory($response);

        $result = self::runCommand('crypto:updatePrices');

        self::assertCommandIsSuccessful($result);
        self::assertSame(1, $http->getRequestsCount());
        $url = $response->getRequestUrl();
        self::assertStringStartsWith('https://api.coingecko.com/api/v3/simple/price?', $url);
        parse_str((string) parse_url($url, \PHP_URL_QUERY), $query);
        self::assertSame('usd', $query['vs_currencies']);
        self::assertEqualsCanonicalizing(['bitcoin', 'ethereum', 'usd-coin'], explode(',', $query['ids']));

        $em->clear();
        $repository = self::getContainer()->get(CryptocurrencyRepository::class);
        self::assertSame(65000.0, $repository->find($data->btc->getId())->getPriceUsd());
        self::assertSame(0.999, $repository->find($data->usdc->getId())->getPriceUsd());
        self::assertSame(2000.0, $repository->find($data->eth->getId())->getPriceUsd());
    }
}
