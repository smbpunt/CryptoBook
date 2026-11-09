<?php

namespace App\Tests\Unit\Service;

use App\Coingecko\Client\CoingeckoClient;
use App\Entity\Cryptocurrency;
use App\Repository\CryptocurrencyRepository;
use App\Service\CryptocurrencyService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * updateDatas() fills a new coin from CoinGecko (called by the prePersist listener).
 */
final class CryptocurrencyServiceTest extends TestCase
{
    public function testUpdateDatasFillsCoinFromCoinGecko(): void
    {
        $response = new JsonMockResponse([
            'name' => 'Bitcoin',
            'symbol' => 'btc',
            'image' => ['thumb' => 'https://img/thumb.png', 'small' => 'https://img/small.png', 'large' => 'https://img/large.png'],
            'market_data' => ['current_price' => ['usd' => 65000], 'market_cap' => ['usd' => 1_200_000_000]],
        ]);
        $http = new MockHttpClient($response, 'https://api.coingecko.com/api/v3/');
        $coin = new Cryptocurrency()->setLibelleCoingecko('bitcoin');

        $this->service($http)->updateDatas($coin);

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

    public function testUpdateDatasSkipsCoinWithoutCoinGeckoId(): void
    {
        $http = new MockHttpClient([]);

        $this->service($http)->updateDatas(new Cryptocurrency());

        self::assertSame(0, $http->getRequestsCount());
    }

    public function testUpdateDatasLogsApiErrors(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('critical');
        $coin = new Cryptocurrency()->setLibelleCoingecko('bitcoin');

        $this->service(new MockHttpClient(new MockResponse('', ['http_code' => 500])), $logger)->updateDatas($coin);

        self::assertNull($coin->getLibelle());
    }

    public function testUpdatePricesLogsApiErrorsWithCoinIds(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('critical')
            ->with(self::stringContains('bitcoin,ethereum'));
        $repository = $this->createStub(CryptocurrencyRepository::class);
        $repository->method('findAll')->willReturn([
            new Cryptocurrency()->setLibelleCoingecko('bitcoin'),
            new Cryptocurrency()->setLibelleCoingecko('ethereum'),
        ]);

        $this->service(new MockHttpClient(new MockResponse('', ['http_code' => 500])), $logger, $repository)->updatePrices();
    }

    private function service(MockHttpClient $http, ?LoggerInterface $logger = null, ?CryptocurrencyRepository $repository = null): CryptocurrencyService
    {
        return new CryptocurrencyService(
            new CoingeckoClient($http),
            $repository ?? $this->createStub(CryptocurrencyRepository::class),
            $this->createStub(EntityManagerInterface::class),
            $logger ?? new NullLogger(),
        );
    }
}
