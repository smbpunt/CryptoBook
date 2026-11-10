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
        $eur = $this->fiatCurrencyRepository->getByFixerKey(FiatCurrency::KEY_EUR);

        return $this->fiatConverter->toUsd((float) $dca->getFiatToDcaEur(), $eur) + (float) $dca->getFarmingToDcaUsd();
    }
}
