<?php

namespace App\Tests\Cryptocurrency\Updater;

use App\Coingecko\Client\CoingeckoClient;
use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Cryptocurrency\Repository\CryptocurrencyRepository;
use App\Cryptocurrency\Updater\CoinPriceUpdater;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CoinPriceUpdaterTest extends TestCase
{
    public function testLogsApiErrorsWithTheCoinIds(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('critical')
            ->with(self::anything(), self::callback(static fn (array $context): bool => 'bitcoin,ethereum' === $context['coins']));
        $repository = $this->createStub(CryptocurrencyRepository::class);
        $repository->method('findAll')->willReturn([
            new Cryptocurrency()->setLibelleCoingecko('bitcoin'),
            new Cryptocurrency()->setLibelleCoingecko('ethereum'),
        ]);

        new CoinPriceUpdater(
            new CoingeckoClient(new MockHttpClient(new MockResponse('', ['http_code' => 500]))),
            $repository,
            $this->createStub(EntityManagerInterface::class),
            $logger,
        )->updateAll();
    }
}
