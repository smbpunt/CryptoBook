<?php

namespace App\FiatCurrency\Updater;

use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Repository\FiatCurrencyRepository;
use App\Fixer\Client\FixerClient;
use App\Fixer\Exception\FixerUnavailableException;

final readonly class FiatRateUpdater
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
    public function updateAll(): void
    {
        $currencies = $this->fiatCurrencyRepository->findAll();

        foreach ($currencies as $currency) {
            $others = array_filter($currencies, static fn (FiatCurrency $c): bool => $c->getId() !== $currency->getId());
            $symbols = array_values(array_map(static fn (FiatCurrency $c): string => (string) $c->getFixerKey(), $others));

            $currency->setRates($this->fixerClient->getLatestRates((string) $currency->getFixerKey(), $symbols));
            $currency->setUpdatedAt(new \DateTimeImmutable());
        }
        // One flush: the rates change together, and the dashboard cache is invalidated once.
        $this->fiatCurrencyRepository->flush();
    }
}
