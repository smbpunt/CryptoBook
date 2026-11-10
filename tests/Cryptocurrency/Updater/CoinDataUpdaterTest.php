<?php

namespace App\Tests\Cryptocurrency\Updater;

use App\Coingecko\Client\CoingeckoClient;
use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Cryptocurrency\Repository\CryptocurrencyRepository;
use App\Cryptocurrency\Updater\CoinDataUpdater;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CoinDataUpdaterTest extends TestCase
{
    public function testFillsTheCoinFromCoinGecko(): void
    {
        $response = new JsonMockResponse([
            'name' => 'Bitcoin',
            'symbol' => 'btc',
            'image' => ['thumb' => 'https://img/thumb.png', 'small' => 'https://img/small.png', 'large' => 'https://img/large.png'],
            'market_data' => ['current_price' => ['usd' => 65000], 'market_cap' => ['usd' => 1_200_000_000]],
        ]);
        $http = new MockHttpClient($response, 'https://api.coingecko.com/api/v3/');
        $coin = new Cryptocurrency()->setLibelleCoingecko('bitcoin');

        $this->updater($http)->update($coin);

        self::assertSame(1, $http->getRequestsCount());
        self::assertSame('/api/v3/coins/bitcoin', parse_url($response->getRequestUrl(), \PHP_URL_PATH));
        self::assertSame('Bitcoin', $coin->getLibelle());
        self::assertSame('btc', $coin->getSymbol());
        self::assertSame(65000.0, $coin->getPriceUsd());
        self::assertSame(1_200_000_000.0, $coin->getMcapUsd());
        self::assertSame('https://img/thumb.png', $coin->getUrlImgThumb());
        self::assertSame('https://img/small.png', $coin->getUrlImgSmall());
        self::assertSame('https://img/large.png', $coin->getUrlImgLarge());
    }

    public function testSkipsACoinWithoutCoinGeckoId(): void
    {
        $http = new MockHttpClient([]);

        $this->updater($http)->update(new Cryptocurrency());

        self::assertSame(0, $http->getRequestsCount());
    }

    public function testLogsApiErrors(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('critical');
        $coin = new Cryptocurrency()->setLibelleCoingecko('bitcoin');

        $this->updater(new MockHttpClient(new MockResponse('', ['http_code' => 500])), $logger)->update($coin);

        self::assertNull($coin->getLibelle());
    }

    private function updater(MockHttpClient $http, ?LoggerInterface $logger = null): CoinDataUpdater
    {
        return new CoinDataUpdater(
            new CoingeckoClient($http),
            $this->createStub(CryptocurrencyRepository::class),
            $this->createStub(EntityManagerInterface::class),
            $logger ?? new NullLogger(),
        );
    }
}
