<?php

namespace App\Service;

use App\Entity\FiatCurrency;
use App\Entity\User;
use App\Fixer\Client\FixerClient;
use App\Fixer\Exception\FixerUnavailableException;
use App\Repository\FiatCurrencyRepository;
use Symfony\Bundle\SecurityBundle\Security;

class FiatExchangeRatesService
{
    public function __construct(
        private readonly FixerClient $fixerClient,
        private readonly FiatCurrencyRepository $fiatCurrencyRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * Updates the rates of every currency against the other ones.
     *
     * @throws FixerUnavailableException
     */
    public function updateFiatRates(): void
    {
        $currencies = $this->fiatCurrencyRepository->findAll();

        foreach ($currencies as $currency) {
            $others = array_filter($currencies, static fn (FiatCurrency $c): bool => $c->getId() !== $currency->getId());
            $symbols = array_values(array_map(static fn (FiatCurrency $c): string => (string) $c->getFixerKey(), $others));

            $currency->setRates($this->fixerClient->getLatestRates((string) $currency->getFixerKey(), $symbols));
            $currency->setUpdatedAt(new \DateTimeImmutable());

            $this->fiatCurrencyRepository->save($currency, true);
        }
    }

    private function getUserFiatCurrency(): FiatCurrency
    {
        // No logged-in user (command, worker): fall back to USD.
        $user = $this->security->getUser();
        if ($user instanceof User && null !== $user->getFavoriteFiatCurrency()) {
            return $user->getFavoriteFiatCurrency();
        }

        return $this->fiatCurrencyRepository->findOneBy(['fixerKey' => FiatCurrency::$KEY_USD]);
    }

    public function getUsdRateFromFavoriteCurrency(): float
    {
        if ($this->getUserFiatCurrency()->getFixerKey() === FiatCurrency::$KEY_USD) {
            return 1.0;
        }
        $userFiatCurrency = $this->getUserFiatCurrency();

        return $userFiatCurrency->getRates()[FiatCurrency::$KEY_USD];
    }

    public function getFavoriteCurrencyRateFromUsd(): float
    {
        $usd = $this->fiatCurrencyRepository->findOneBy(['fixerKey' => FiatCurrency::$KEY_USD]);

        return $usd->getRates()[$this->getUserFiatCurrency()->getFixerKey()];
    }

    public function toUsd(float $amount, ?FiatCurrency $from = null): float
    {
        $from ??= $this->getUserFiatCurrency();
        if ($from->getFixerKey() === FiatCurrency::$KEY_USD) {
            return $amount;
        }

        return $amount * $from->getRates()[FiatCurrency::$KEY_USD];
    }

    public function toUsdByKey(float $amount, ?string $keyFrom = null): float
    {
        $from = $this->fiatCurrencyRepository->findOneBy(['fixerKey' => $keyFrom ?? $this->getUserFiatCurrency()->getFixerKey()]);

        return $this->toUsd($amount, $from);
    }

    public function toFavoriteCurrency(float $amount): float
    {
        if ($this->getUserFiatCurrency()->getFixerKey() === FiatCurrency::$KEY_USD) {
            return $amount;
        }
        $usd = $this->fiatCurrencyRepository->findOneBy(['fixerKey' => FiatCurrency::$KEY_USD]);

        return $amount * $usd->getRates()[$this->getUserFiatCurrency()->getFixerKey()];
    }
}
