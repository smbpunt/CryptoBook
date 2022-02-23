<?php

namespace App\Command;

use App\Service\CryptocurrencyService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CryptoUpdatePricesCommand extends Command
{
    protected static $defaultName = 'crypto:updatePrices';
    protected static $defaultDescription = 'Mets à jour les prix de tout le catalogue cryptos.';
    private CryptocurrencyService $cryptocurrencyService;

    /**
     * @param CryptocurrencyService $cryptocurrencyService
     */
    public function __construct(CryptocurrencyService $cryptocurrencyService)
    {
        $this->cryptocurrencyService = $cryptocurrencyService;
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
       // $this->cryptocurrencyService->updatePrices();
        $this->cryptocurrencyService->updateAllCryptos();

        $io->success('Execution de crypto:updatePrices terminé.');
        return Command::SUCCESS;
    }
}
