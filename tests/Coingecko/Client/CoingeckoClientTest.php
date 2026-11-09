<?php

namespace App\Tests\Coingecko\Client;

use App\Coingecko\Client\CoingeckoClient;
use App\Coingecko\Dto\Coin;
use App\Coingecko\Exception\CoingeckoUnavailableException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CoingeckoClientTest extends TestCase
{
    private const string BASE_URI = 'https://api.coingecko.com/api/v3/';

    public function testGetCoinMapsTheAnswer(): void
    {
        $http = new MockHttpClient(new JsonMockResponse([
            'name' => 'Bitcoin',
            'symbol' => 'btc',
            'image' => ['thumb' => 'https://img/thumb.png', 'small' => 'https://img/small.png', 'large' => 'https://img/large.png'],
            'market_data' => ['current_price' => ['usd' => 65000], 'market_cap' => ['usd' => 1.2e12]],
        ]), self::BASE_URI);

        $coin = new CoingeckoClient($http)->getCoin('bitcoin');

        self::assertEquals(new Coin('Bitcoin', 'btc', 65000.0, 1.2e12, 'https://img/thumb.png', 'https://img/small.png', 'https://img/large.png'), $coin);
    }

    public function testGetCoinCallsTheCoinEndpointWithoutHeavyData(): void
    {
        $response = new JsonMockResponse([]);
        $http = new MockHttpClient($response, self::BASE_URI);

        new CoingeckoClient($http)->getCoin('usd-coin');

        $url = $response->getRequestUrl();
        self::assertStringStartsWith(self::BASE_URI.'coins/usd-coin?', $url);
        parse_str((string) parse_url($url, \PHP_URL_QUERY), $query);
        self::assertSame('false', $query['tickers']);
    }

    public function testGetCoinLeavesMissingFieldsNull(): void
    {
        $http = new MockHttpClient(new JsonMockResponse(['name' => 'Bitcoin', 'image' => ['thumb' => '']]), self::BASE_URI);

        self::assertEquals(new Coin(name: 'Bitcoin'), new CoingeckoClient($http)->getCoin('bitcoin'));
    }

    public function testGetUsdPricesReturnsKnownCoinsOnly(): void
    {
        $response = new JsonMockResponse(['bitcoin' => ['usd' => 65000], 'usd-coin' => ['usd' => 0.999], 'weird' => ['eur' => 1]]);
        $http = new MockHttpClient($response, self::BASE_URI);

        $prices = new CoingeckoClient($http)->getUsdPrices(['bitcoin', 'ethereum', 'usd-coin', 'weird']);

        self::assertSame(['bitcoin' => 65000.0, 'usd-coin' => 0.999], $prices);
        parse_str((string) parse_url($response->getRequestUrl(), \PHP_URL_QUERY), $query);
        self::assertSame('bitcoin,ethereum,usd-coin,weird', $query['ids']);
        self::assertSame('usd', $query['vs_currencies']);
    }

    public function testGetUsdPricesWithoutIdsDoesNotCallTheApi(): void
    {
        $http = new MockHttpClient([]);

        self::assertSame([], new CoingeckoClient($http)->getUsdPrices([]));
        self::assertSame(0, $http->getRequestsCount());
    }

    public function testHttpErrorsBecomeUnavailableException(): void
    {
        $http = new MockHttpClient(new MockResponse('', ['http_code' => 500]), self::BASE_URI);

        $this->expectException(CoingeckoUnavailableException::class);
        new CoingeckoClient($http)->getCoin('bitcoin');
    }

    public function testNonJsonAnswerBecomesUnavailableException(): void
    {
        $http = new MockHttpClient(new MockResponse('<html>Too many requests</html>'), self::BASE_URI);

        $this->expectException(CoingeckoUnavailableException::class);
        new CoingeckoClient($http)->getUsdPrices(['bitcoin']);
    }
}
