<?php

namespace App\Service;

use App\Entity\Cryptocurrency;
use App\Repository\CryptocurrencyRepository;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class CryptocurrencyService
{

    private CoinGeckoClient $client;
    private CryptocurrencyRepository $repoCryptocurrency;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    /**
     * @param CoinGeckoClient $client
     * @param CryptocurrencyRepository $repoCryptocurrency
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(CoinGeckoClient $client, CryptocurrencyRepository $repoCryptocurrency, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->repoCryptocurrency = $repoCryptocurrency;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function updateAllCryptos(bool $onlyPrice = true): void
    {
        $cryptos = $this->repoCryptocurrency->findAll();
        foreach ($cryptos as $cryptocurrency) {
            $this->updateDatas($cryptocurrency, $onlyPrice);
        }
    }

    public function updateDatas(Cryptocurrency $cryptocurrency, bool $onlyPrices = true): void
    {
        $libelleCG = $cryptocurrency->getLibelleCoingecko();
        if (is_null($libelleCG)) {
            return;
        }

        try {
            $datas = $this->client->coins()->getCoin($libelleCG);

            if (is_array($datas)) {
                if (array_key_exists('market_data', $datas)) {
                    if (array_key_exists('current_price', $datas['market_data'])) {
                        if (array_key_exists('usd', $datas['market_data']['current_price'])) {
                            $cryptocurrency->setPriceUsd($datas['market_data']['current_price']['usd']);
                        }
                        if (array_key_exists('eur', $datas['market_data']['current_price'])) {
                            $cryptocurrency->setPriceEur($datas['market_data']['current_price']['eur']);
                        }
                    }

                    if (!$onlyPrices) {
                        if (array_key_exists('name', $datas)) {
                            $cryptocurrency->setLibelle($datas['name']);
                        }
                        if (array_key_exists('symbol', $datas)) {
                            $cryptocurrency->setSymbol($datas['symbol']);
                        }

                        if (array_key_exists('image', $datas)) {
                            if (array_key_exists('thumb', $datas['image'])) {
                                $cryptocurrency->setUrlImgThumb($datas['image']['thumb']);
                            }
                            if (array_key_exists('small', $datas['image'])) {
                                $cryptocurrency->setUrlImgSmall($datas['image']['small']);
                            }
                            if (array_key_exists('large', $datas['image'])) {
                                $cryptocurrency->setUrlImgLarge($datas['image']['large']);
                            }
                        }

                        if (array_key_exists('market_cap', $datas['market_data'])) {
                            if (array_key_exists('usd', $datas['market_data']['market_cap'])) {
                                $cryptocurrency->setMcapUsd($datas['market_data']['market_cap']['usd']);
                            }
                            if (array_key_exists('eur', $datas['market_data']['market_cap'])) {
                                $cryptocurrency->setMcapEur($datas['market_data']['market_cap']['eur']);
                            }
                        }
                    }
                }
            }

            $this->entityManager->persist($cryptocurrency);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->logger->critical('Erreur lors de la récupération des données pour le coin ' . $cryptocurrency->getLibelleCoingecko() . '. Exception : ' . $e->getMessage());
        }
    }


}