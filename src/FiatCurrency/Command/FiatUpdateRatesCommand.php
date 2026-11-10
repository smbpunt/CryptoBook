<?php

namespace App\FiatCurrency\Command;

use App\FiatCurrency\Updater\FiatRateUpdater;
use App\Fixer\Exception\FixerUnavailableException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'fiat:updateRates', description: 'Updates the exchange rates of every fiat currency from Fixer')]
final readonly class FiatUpdateRatesCommand
{
    public function __construct(private FiatRateUpdater $fiatRateUpdater)
    {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        try {
            $this->fiatRateUpdater->updateAll();
        } catch (FixerUnavailableException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success('Fiat rates updated from Fixer.');

        return Command::SUCCESS;
    }
}
