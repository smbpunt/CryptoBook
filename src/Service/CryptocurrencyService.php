<?php

namespace App\Service;

use App\Coingecko\Client\CoingeckoClient;
use App\Coingecko\Exception\CoingeckoUnavailableException;
use App\Entity\Cryptocurrency;
use App\Repository\CryptocurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CryptocurrencyService
{
    public function __construct(
        private readonly CoingeckoClient $client,
        private readonly CryptocurrencyRepository $repoCryptocurrency,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function updateAllCryptos(): void
    {
        $cryptos = $this->repoCryptocurrency->findAll();
        foreach ($cryptos as $cryptocurrency) {
            $this->updateDatas($cryptocurrency);
        }
        $this->entityManager->flush();
    }

    /**
     * Mets à jour toutes les infos de $cryptocurrency.
     */
    public function updateDatas(Cryptocurrency $cryptocurrency): void
    {
        $libelleCG = $cryptocurrency->getLibelleCoingecko();
        if (null === $libelleCG) {
            return;
        }

        try {
            $coin = $this->client->getCoin($libelleCG);
        } catch (CoingeckoUnavailableException $e) {
            $this->logger->critical('Erreur lors de la récupération des données pour le coin '.$libelleCG.'. Exception : '.$e->getMessage());

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

    /**
     * Mets à jour seulement les prix de tout le catalogue crypto.
     */
    public function updatePrices(): void
    {
        $cryptos = $this->repoCryptocurrency->findAll();
        $libelles = array_values(array_filter(array_map(static fn ($crypto) => $crypto->getLibelleCoingecko(), $cryptos)));
        try {
            $prices = $this->client->getUsdPrices($libelles);
        } catch (CoingeckoUnavailableException $e) {
            $this->logger->critical('Erreur lors de la récupération des prix pour '.implode(',', $libelles).'. Exception : '.$e->getMessage());

            return;
        }

        foreach ($cryptos as $crypto) {
            $libelleCg = $crypto->getLibelleCoingecko();
            if (null !== $libelleCg && \array_key_exists($libelleCg, $prices)) {
                $crypto->setPriceUsd($prices[$libelleCg]);
            } else {
                $this->logger->warning('Erreur lors de la récupération des prix pour '.$libelleCg);
            }
        }
        $this->entityManager->flush();
    }
}
