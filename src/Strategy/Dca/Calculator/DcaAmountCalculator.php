<?php

namespace App\Strategy\Dca\Calculator;

use App\FiatCurrency\Converter\FiatConverter;
use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Exception\MissingRateException;
use App\FiatCurrency\Repository\FiatCurrencyRepository;
use App\Strategy\Dca\Entity\StrategyDca;

final readonly class DcaAmountCalculator
{
    public function __construct(
        private FiatConverter $fiatConverter,
        private FiatCurrencyRepository $fiatCurrencyRepository,
    ) {
    }

    /**
     * Amount to invest at each DCA, in USD: the euros at the Fixer rate, plus the farming rewards.
     * Null while the EUR rate is missing: the DCA page stays usable, only its total is unknown.
     */
    public function amountUsd(StrategyDca $dca): ?float
    {
        $fiatUsd = 0.0;
        if ($dca->getFiatToDcaEur() > 0) {
            $eur = $this->fiatCurrencyRepository->getByFixerKey(FiatCurrency::KEY_EUR);
            try {
                $fiatUsd = $this->fiatConverter->toUsd($dca->getFiatToDcaEur(), $eur);
            } catch (MissingRateException) {
                return null;
            }
        }

        return $fiatUsd + (float) $dca->getFarmingToDcaUsd();
    }
}
