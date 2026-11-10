<?php

namespace App\Strategy\Dca\Calculator;

use App\FiatCurrency\Converter\FiatConverter;
use App\FiatCurrency\Entity\FiatCurrency;
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
     */
    public function amountUsd(StrategyDca $dca): float
    {
        $fiatUsd = 0.0;
        if ($dca->getFiatToDcaEur() > 0) {
            $eur = $this->fiatCurrencyRepository->getByFixerKey(FiatCurrency::KEY_EUR);
            $fiatUsd = $this->fiatConverter->toUsd($dca->getFiatToDcaEur(), $eur);
        }

        return $fiatUsd + (float) $dca->getFarmingToDcaUsd();
    }
}
