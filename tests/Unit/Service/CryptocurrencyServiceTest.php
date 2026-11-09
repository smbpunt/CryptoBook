<?php

namespace App\Tests\Unit\Service;

use App\Entity\Cryptocurrency;
use App\Repository\CryptocurrencyRepository;
use App\Service\CryptocurrencyService;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * updateDatas() fills a new coin from CoinGecko (called by the prePersist listener).
 */
final class CryptocurrencyServiceTest extends TestCase
{
    public function testUpdateDatasFillsCoinFromCoinGecko(): void
    {
        $requests = [];
        $mock = new MockHandler([static function (Request $request) use (&$requests): Response {
            $requests[] = $request;

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'name' => 'Bitcoin',
                'symbol' => 'btc',
                'image' => ['thumb' => 'https://img/thumb.png', 'small' => 'https://img/small.png', 'large' => 'https://img/large.png'],
                'market_data' => ['current_price' => ['usd' => 65000], 'market_cap' => ['usd' => 1_200_000_000]],
            ], \JSON_THROW_ON_ERROR));
        }]);
        $coin = (new Cryptocurrency())->setLibelleCoingecko('bitcoin');

        $this->service($mock)->updateDatas($coin);

        self::assertCount(1, $requests);
        self::assertSame('/api/v3/coins/bitcoin', $requests[0]->getUri()->getPath());
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
        $mock = new MockHandler([]);

        $this->service($mock)->updateDatas(new Cryptocurrency());

        self::assertSame(0, $mock->count());
    }

    public function testUpdateDatasLogsApiErrors(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('critical');
        $coin = (new Cryptocurrency())->setLibelleCoingecko('bitcoin');

        $this->service(new MockHandler([new Response(500)]), $logger)->updateDatas($coin);

        self::assertNull($coin->getLibelle());
    }

    private function service(MockHandler $mock, ?LoggerInterface $logger = null): CryptocurrencyService
    {
        $client = new CoinGeckoClient(new Client(['base_uri' => 'https://api.coingecko.com', 'handler' => HandlerStack::create($mock)]));

        return new CryptocurrencyService(
            $client,
            $this->createStub(CryptocurrencyRepository::class),
            $this->createStub(EntityManagerInterface::class),
            $logger ?? new NullLogger(),
        );
    }
}
