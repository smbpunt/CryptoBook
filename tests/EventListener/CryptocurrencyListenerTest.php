<?php

namespace App\Tests\EventListener;

use App\Entity\Cryptocurrency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

final class CryptocurrencyListenerTest extends KernelTestCase
{
    public function testNewCoinWithoutNameIsFilledFromCoinGecko(): void
    {
        $response = new JsonMockResponse([
            'name' => 'Bitcoin',
            'symbol' => 'btc',
            'image' => ['thumb' => 'https://img/thumb.png', 'small' => 'https://img/small.png', 'large' => 'https://img/large.png'],
            'market_data' => ['current_price' => ['usd' => 65000], 'market_cap' => ['usd' => 1.2e12]],
        ]);
        $http = self::getContainer()->get('http_client.transport');
        \assert($http instanceof MockHttpClient);
        $http->setResponseFactory($response);

        $coin = new Cryptocurrency()->setLibelleCoingecko('bitcoin');
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($coin);

        self::assertStringStartsWith('https://api.coingecko.com/api/v3/coins/bitcoin?', $response->getRequestUrl());
        self::assertSame('Bitcoin', $coin->getLibelle());
        self::assertSame(65000.0, $coin->getPriceUsd());
    }

    public function testCoinWithANameIsLeftAlone(): void
    {
        $http = self::getContainer()->get('http_client.transport');
        \assert($http instanceof MockHttpClient);

        $coin = new Cryptocurrency()->setLibelleCoingecko('bitcoin')->setLibelle('Bitcoin');
        self::getContainer()->get(EntityManagerInterface::class)->persist($coin);

        self::assertSame(0, $http->getRequestsCount());
    }
}
