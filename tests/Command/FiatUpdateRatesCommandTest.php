<?php

namespace App\Tests\Command;

use App\Repository\FiatCurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Relies on the USD, EUR and AUD currencies seeded by the migrations.
 */
final class FiatUpdateRatesCommandTest extends KernelTestCase
{
    public function testUpdatesRatesOfEveryCurrencyAgainstTheOthers(): void
    {
        $requestedUrls = [];
        $http = self::getContainer()->get('http_client.transport');
        \assert($http instanceof MockHttpClient);
        $http->setResponseFactory(static function (string $method, string $url, array $options) use (&$requestedUrls): MockResponse {
            $requestedUrls[] = $url;
            self::assertContains('apikey: test-key', $options['headers']);
            parse_str((string) parse_url($url, \PHP_URL_QUERY), $query);
            $symbols = explode(',', $query['symbols']);

            return new JsonMockResponse(['rates' => array_fill_keys($symbols, 1.5)]);
        });
        $repository = self::getContainer()->get(FiatCurrencyRepository::class);

        $result = self::runCommand('fiat:updateRates');

        self::assertCommandIsSuccessful($result);
        self::assertCount(3, $requestedUrls);
        foreach ($requestedUrls as $url) {
            self::assertStringStartsWith('https://api.apilayer.com/fixer/latest?base=', $url);
        }

        self::getContainer()->get(EntityManagerInterface::class)->clear();
        $expected = ['USD' => ['AUD', 'EUR'], 'EUR' => ['AUD', 'USD'], 'AUD' => ['EUR', 'USD']];
        foreach ($expected as $key => $others) {
            $currency = $repository->findOneBy(['fixerKey' => $key]);
            $rates = $currency->getRates();
            ksort($rates);
            self::assertSame(array_fill_keys($others, 1.5), $rates, $key);
            self::assertEqualsWithDelta(new \DateTimeImmutable(), $currency->getUpdatedAt(), 60);
        }
    }
}
