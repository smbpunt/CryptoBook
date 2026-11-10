<?php

namespace App\Cryptocurrency\Updater;

use App\Coingecko\Client\CoingeckoClient;
use App\Coingecko\Exception\CoingeckoUnavailableException;
use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Cryptocurrency\Repository\CryptocurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Updates the USD price of every coin from CoinGecko, in one call.
 */
final readonly class CoinPriceUpdater
{
    public function __construct(
        private CoingeckoClient $client,
        private CryptocurrencyRepository $cryptocurrencyRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function updateAll(): void
    {
        $coins = $this->cryptocurrencyRepository->findAll();
        $ids = array_values(array_filter(array_map(static fn (Cryptocurrency $coin): ?string => $coin->getLibelleCoingecko(), $coins)));

        try {
            $prices = $this->client->getUsdPrices($ids);
        } catch (CoingeckoUnavailableException $e) {
            $this->logger->critical('Cannot fetch the CoinGecko prices of {coins}: {error}', ['coins' => implode(',', $ids), 'error' => $e->getMessage()]);

            return;
        }

        foreach ($coins as $coin) {
            $id = $coin->getLibelleCoingecko();
            if (null !== $id && \array_key_exists($id, $prices)) {
                $coin->setPriceUsd($prices[$id]);
            } else {
                $this->logger->warning('No CoinGecko price for {coin}', ['coin' => $id]);
            }
        }
        $this->entityManager->flush();
    }
}
