<?php

namespace App\Service;

use App\Entity\FiatCurrency;
use App\Repository\FiatCurrencyRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FiatExchangeRatesService
{
    /**
     * @var HttpClientInterface
     */
    private HttpClientInterface $client;

    private FiatCurrencyRepository $fiatCurrencyRepository;

    private Security $security;

    /**
     * @param HttpClientInterface $client
     * @param FiatCurrencyRepository $fiatCurrencyRepository
     * @param Security $security
     */
    public function __construct(HttpClientInterface $client, FiatCurrencyRepository $fiatCurrencyRepository, Security $security)
    {
        $this->client = $client;
        $this->fiatCurrencyRepository = $fiatCurrencyRepository;
        $this->security = $security;
    }

    /**
     * @return void
     * Mets à jour les taux de change des devises
     */
    public function updateFiatRates(): void
    {
        $currencies = $this->fiatCurrencyRepository->findAll();

        foreach ($currencies as $currency) {
            $tos = array_filter($currencies, function ($c) use ($currency) {
                return $c->getId() !== $currency->getId();
            });

            $stringtos = implode(',', array_map(function ($c) {
                return $c->getFixerKey();
            }, $tos));

            $response = $this->client->withOptions([
                'base_uri' => 'https://api.apilayer.com/fixer/',
                'headers' => [
                    'apikey' => 'wBhE54673wmOYLv8esarT6o4UhkDvpsh'
                ]
            ])->request('GET', 'latest?base=' . $currency->getFixerKey() . "&symbols=$stringtos");
            $content = $response->toArray();
            $currency->setRates($content['rates']);
            $currency->setUpdatedAt(new \DateTimeImmutable());

            $this->fiatCurrencyRepository->save($currency, true);
        }
    }

    private function getUserFiatCurrency(): FiatCurrency
    {
        $user = $this->security->getUser();
        if ($user->getFavoriteFiatCurrency() !== null) {
            return $user->getFavoriteFiatCurrency();
        }
        return $this->fiatCurrencyRepository->findOneBy(['fixerKey' => FiatCurrency::$KEY_USD]);
    }

    public function getUsdRateFromFavoriteCurrency(): float
    {
        if ($this->getUserFiatCurrency()->getFixerKey() === FiatCurrency::$KEY_USD) return 1.0;
        $userFiatCurrency = $this->getUserFiatCurrency();
        return $userFiatCurrency->getRates()[FiatCurrency::$KEY_USD];
    }

    public function getFavoriteCurrencyRateFromUsd(): float
    {
        $usd = $this->fiatCurrencyRepository->findOneBy(['fixerKey' => FiatCurrency::$KEY_USD]);
        return $usd->getRates()[$this->getUserFiatCurrency()->getFixerKey()];
    }

    public function toUsd(float $amount, FiatCurrency $from = null): float
    {
        return $amount * ($from == null ? $this->getUserFiatCurrency()->getRates()[FiatCurrency::$KEY_USD] : $from->getRates()[FiatCurrency::$KEY_USD]);
    }

    public function toUsdByKey(float $amount, string $keyFrom = null): float
    {
        $from = $this->fiatCurrencyRepository->findOneBy(['fixerKey' => $keyFrom ?? $this->getUserFiatCurrency()->getFixerKey()]);
        return $this->toUsd($amount, $from);
    }

    public function toFavoriteCurrency(float $amount): float
    {
        if ($this->getUserFiatCurrency()->getFixerKey() === FiatCurrency::$KEY_USD) return $amount;
        $usd = $this->fiatCurrencyRepository->findOneBy(['fixerKey' => FiatCurrency::$KEY_USD]);
        return $amount * $usd->getRates()[$this->getUserFiatCurrency()->getFixerKey()];
    }
}