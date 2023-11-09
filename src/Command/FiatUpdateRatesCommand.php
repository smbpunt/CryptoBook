<?php

namespace App\Command;

use App\Service\FiatExchangeRatesService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'fiat:updateRates',
    description: 'Update fiat rates',
)]
class FiatUpdateRatesCommand extends Command
{

    private FiatExchangeRatesService $fiatExchangeRatesService;

    /**
     * @param FiatExchangeRatesService $fiatExchangeRatesService
     */
    public function __construct(FiatExchangeRatesService $fiatExchangeRatesService)
    {
        $this->fiatExchangeRatesService = $fiatExchangeRatesService;
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->fiatExchangeRatesService->updateFiatRates();
        $io->success('Execution de fiat:updateRates terminé.');
        return Command::SUCCESS;
    }
}
