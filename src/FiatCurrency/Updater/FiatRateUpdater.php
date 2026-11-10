<?php

namespace App\FiatCurrency\Updater;

use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Exception\RatesUnavailableException;
use App\FiatCurrency\Repository\FiatCurrencyRepository;
use App\Fixer\Client\FixerClient;
use App\Fixer\Exception\FixerUnavailableException;
use Psr\Log\LoggerInterface;

/**
 * Rates of each currency against the other ones, from Fixer. A currency keeps its rates
 * until Fixer gives new ones, its USD rate included: the converter needs nothing else.
 */
final readonly class FiatRateUpdater
{
    public function __construct(
        private FixerClient $fixerClient,
        private FiatCurrencyRepository $fiatCurrencyRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Updates every currency it can, and leaves the others as they are.
     *
     * @return array<string, string> error message of each currency left as it was, by Fixer key
     */
    public function updateAll(): array
    {
        $currencies = $this->fiatCurrencyRepository->findAll();
        $keys = self::keys($currencies);

        $failures = [];
        foreach ($currencies as $currency) {
            try {
                $this->fetch($currency, $keys);
            } catch (RatesUnavailableException $exception) {
                $failures[(string) $currency->getFixerKey()] = $exception->getMessage();
                $this->logger->error('Rates of {currency} not updated: {message}', [
                    'currency' => $currency->getFixerKey(),
                    'message' => $exception->getMessage(),
                    'exception' => $exception,
                ]);
            }
        }
        // One flush: the rates change together, and the dashboard cache is invalidated once.
        $this->fiatCurrencyRepository->flush();

        return $failures;
    }

    /**
     * Sets the rates of the currency, new or not, without flushing.
     *
     * @throws RatesUnavailableException when Fixer fails or gives no USD rate
     */
    public function update(FiatCurrency $currency): void
    {
        $this->fetch($currency, self::keys($this->fiatCurrencyRepository->findAll()));
    }

    /**
     * @param list<string> $keys every known currency, the USD included
     *
     * @throws RatesUnavailableException
     */
    private function fetch(FiatCurrency $currency, array $keys): void
    {
        $key = (string) $currency->getFixerKey();
        $symbols = array_values(array_diff($keys, [$key]));

        try {
            $rates = $this->fixerClient->getLatestRates($key, $symbols);
        } catch (FixerUnavailableException $exception) {
            throw RatesUnavailableException::for($key, $exception->getMessage(), $exception);
        }
        if (FiatCurrency::KEY_USD !== $key && !\array_key_exists(FiatCurrency::KEY_USD, $rates)) {
            throw RatesUnavailableException::for($key, 'Fixer gave no USD rate.');
        }

        $currency->setRates($rates);
        $currency->setUpdatedAt(new \DateTimeImmutable());
    }

    /**
     * @param list<FiatCurrency> $currencies
     *
     * @return list<string>
     */
    private static function keys(array $currencies): array
    {
        $keys = array_map(static fn (FiatCurrency $currency): string => (string) $currency->getFixerKey(), $currencies);

        return array_values(array_unique([FiatCurrency::KEY_USD, ...$keys]));
    }
}
