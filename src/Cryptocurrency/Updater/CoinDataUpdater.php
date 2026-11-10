<?php

namespace App\Cryptocurrency\Updater;

use App\Coingecko\Client\CoingeckoClient;
use App\Coingecko\Exception\CoingeckoUnavailableException;
use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Cryptocurrency\Repository\CryptocurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Fills the name, symbol, price, market cap and images of the coins from CoinGecko.
 */
final readonly class CoinDataUpdater
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
        foreach ($this->cryptocurrencyRepository->findAll() as $cryptocurrency) {
            $this->update($cryptocurrency);
        }
        $this->entityManager->flush();
    }

    /**
     * Updates the coin without flushing. A coin without CoinGecko id is left alone.
     */
    public function update(Cryptocurrency $cryptocurrency): void
    {
        $coingeckoId = $cryptocurrency->getLibelleCoingecko();
        if (null === $coingeckoId) {
            return;
        }

        try {
            $coin = $this->client->getCoin($coingeckoId);
        } catch (CoingeckoUnavailableException $e) {
            $this->logger->critical('Cannot fetch the CoinGecko data of {coin}: {error}', ['coin' => $coingeckoId, 'error' => $e->getMessage()]);

            return;
        }

        if (null !== $coin->priceUsd) {
            $cryptocurrency->setPriceUsd($coin->priceUsd);
        }
        if (null !== $coin->name) {
            $cryptocurrency->setLibelle($coin->name);
        }
        if (null !== $coin->symbol) {
            $cryptocurrency->setSymbol($coin->symbol);
        }
        if (null !== $coin->imageThumb) {
            $cryptocurrency->setUrlImgThumb($coin->imageThumb);
        }
        if (null !== $coin->imageSmall) {
            $cryptocurrency->setUrlImgSmall($coin->imageSmall);
        }
        if (null !== $coin->imageLarge) {
            $cryptocurrency->setUrlImgLarge($coin->imageLarge);
        }
        if (null !== $coin->marketCapUsd) {
            $cryptocurrency->setMcapUsd($coin->marketCapUsd);
        }
    }
}
