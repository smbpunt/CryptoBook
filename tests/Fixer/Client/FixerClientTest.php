<?php

namespace App\Tests\Fixer\Client;

use App\Fixer\Client\FixerClient;
use App\Fixer\Exception\FixerUnavailableException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

final class FixerClientTest extends TestCase
{
    private const string BASE_URI = 'https://api.apilayer.com/fixer/';

    public function testGetLatestRatesCallsTheLatestEndpoint(): void
    {
        $response = new JsonMockResponse(['rates' => ['EUR' => 0.9, 'AUD' => 1.5, 'BAD' => 'x']]);
        $http = new MockHttpClient($response, self::BASE_URI);

        $rates = new FixerClient($http)->getLatestRates('USD', ['EUR', 'AUD']);

        self::assertSame(['EUR' => 0.9, 'AUD' => 1.5], $rates);
        self::assertSame(self::BASE_URI.'latest?base=USD&symbols=EUR%2CAUD', $response->getRequestUrl());
    }

    public function testGetLatestRatesThrowsWhenFixerFails(): void
    {
        $http = new MockHttpClient(new MockResponse('', ['http_code' => 500]), self::BASE_URI);

        $this->expectException(FixerUnavailableException::class);

        new FixerClient($http)->getLatestRates('USD', ['EUR']);
    }

    public function testGetLatestRatesThrowsWithoutRates(): void
    {
        $http = new MockHttpClient(new JsonMockResponse(['success' => false]), self::BASE_URI);

        $this->expectException(FixerUnavailableException::class);

        new FixerClient($http)->getLatestRates('USD', ['EUR']);
    }
}
