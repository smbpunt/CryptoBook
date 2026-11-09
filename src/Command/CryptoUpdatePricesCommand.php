<?php

namespace App\Command;

use App\Service\CryptocurrencyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'crypto:updatePrices',
    description: 'Mets à jour les prix de tout le catalogue cryptos',
)]
class CryptoUpdatePricesCommand extends Command
{
    public function __construct(private readonly CryptocurrencyService $cryptocurrencyService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->cryptocurrencyService->updatePrices();
        $io->success('Execution de crypto:updatePrices terminé.');

        return Command::SUCCESS;
    }
}
