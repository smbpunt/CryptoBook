<?php

namespace App\Command;

use App\Service\CryptocurrencyService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CryptoUpdatePricesCommand extends Command
{
    private CryptocurrencyService $cryptocurrencyService;
    protected static $defaultName = 'crypto:updatePrices';
    protected static $defaultDescription = 'Add a short description for your command';

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

        $this->cryptocurrencyService->updateAllCryptos();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
