<?php

namespace App\Service;

use App\Entity\User;
use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Repository\FiatCurrencyRepository;
use App\Fixer\Client\FixerClient;
use App\Fixer\Exception\FixerUnavailableException;

final readonly class FiatExchangeRatesService
{
    public function __construct(
        private FixerClient $fixerClient,
        private FiatCurrencyRepository $fiatCurrencyRepository,
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

            $this->fiatCurrencyRepository->save($currency);
        }
        // One flush: the rates change together, and the dashboard cache is invalidated once.
        $this->fiatCurrencyRepository->flush();
    }

    /**
     * Value of one unit of the user's favorite currency in USD.
     */
    public function getUsdRateFromFavoriteCurrency(User $user): float
    {
        return $this->toUsd(1.0, $user);
    }

    /**
     * Converts an amount in the user's favorite currency to USD.
     */
    public function toUsd(float $amount, User $user): float
    {
        $from = $this->getFavoriteCurrency($user);
        if ($from->getFixerKey() === FiatCurrency::$KEY_USD) {
            return $amount;
        }

        return $amount * $from->getRates()[FiatCurrency::$KEY_USD];
    }

    /**
     * Converts an amount in USD to the user's favorite currency.
     */
    public function toFavoriteCurrency(float $amountUsd, User $user): float
    {
        $to = $this->getFavoriteCurrency($user);
        if ($to->getFixerKey() === FiatCurrency::$KEY_USD) {
            return $amountUsd;
        }
        $usd = $this->fiatCurrencyRepository->findOneBy(['fixerKey' => FiatCurrency::$KEY_USD]);

        return $amountUsd * $usd->getRates()[$to->getFixerKey()];
    }

    /**
     * Favorite currency of the user, USD when none is set.
     */
    public function getFavoriteCurrency(User $user): FiatCurrency
    {
        return $user->getFavoriteFiatCurrency()
            ?? $this->fiatCurrencyRepository->findOneBy(['fixerKey' => FiatCurrency::$KEY_USD]);
    }
}
