<?php

namespace App\FiatCurrency\Command;

use App\FiatCurrency\Updater\FiatRateUpdater;
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
        $failures = $this->fiatRateUpdater->updateAll();

        if ([] !== $failures) {
            // The other currencies are updated: only these keep their previous rates.
            $io->error(array_map(
                static fn (string $key, string $message): string => \sprintf('%s: %s', $key, $message),
                array_keys($failures),
                $failures,
            ));

            return Command::FAILURE;
        }

        $io->success('Fiat rates updated from Fixer.');

        return Command::SUCCESS;
    }
}
