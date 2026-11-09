<?php

namespace App\Command;

use App\Service\FiatExchangeRatesService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'fiat:updateRates', description: 'Updates the exchange rates of every fiat currency from Fixer')]
final readonly class FiatUpdateRatesCommand
{
    public function __construct(private FiatExchangeRatesService $fiatExchangeRatesService)
    {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $this->fiatExchangeRatesService->updateFiatRates();
        $io->success('Fiat rates updated from Fixer.');

        return Command::SUCCESS;
    }
}
