<?php

namespace App\Tests\Command;

use App\Command\CryptoUpdatePricesCommand;
use App\Repository\CryptocurrencyRepository;
use App\Service\CryptocurrencyService;
use App\Tests\Fixtures\Dataset;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CryptoUpdatePricesCommandTest extends KernelTestCase
{
    public function testUpdatesPricesFromCoinGecko(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $data = Dataset::load($em);

        $requests = [];
        $mock = new MockHandler([
            static function (Request $request) use (&$requests): Response {
                $requests[] = $request;

                // ethereum is missing from the answer: its price must stay untouched.
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'bitcoin' => ['usd' => 65000],
                    'usd-coin' => ['usd' => 0.999],
                ], \JSON_THROW_ON_ERROR));
            },
        ]);
        $client = new CoinGeckoClient(new Client(['base_uri' => 'https://api.coingecko.com', 'handler' => HandlerStack::create($mock)]));
        $service = new CryptocurrencyService($client, self::getContainer()->get(CryptocurrencyRepository::class), $em, new NullLogger());

        $tester = new CommandTester(new CryptoUpdatePricesCommand($service));
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
        self::assertCount(1, $requests);
        self::assertSame('/api/v3/simple/price', $requests[0]->getUri()->getPath());
        parse_str($requests[0]->getUri()->getQuery(), $query);
        self::assertSame('usd', $query['vs_currencies']);
        self::assertEqualsCanonicalizing(['bitcoin', 'ethereum', 'usd-coin'], explode(',', $query['ids']));

        $em->clear();
        $repository = self::getContainer()->get(CryptocurrencyRepository::class);
        self::assertSame(65000.0, $repository->find($data->btc->getId())->getPriceUsd());
        self::assertSame(0.999, $repository->find($data->usdc->getId())->getPriceUsd());
        self::assertSame(2000.0, $repository->find($data->eth->getId())->getPriceUsd());
    }
}
