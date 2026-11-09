<?php

namespace App\Command;

use App\Service\CryptocurrencyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'crypto:updateDatas',
    description: 'Updates name, symbol, price, market cap and images of every coin from CoinGecko',
)]
class CryptoUpdateDatasCommand extends Command
{
    public function __construct(private readonly CryptocurrencyService $cryptocurrencyService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->cryptocurrencyService->updateAllCryptos();
        $io->success('Coins updated from CoinGecko.');

        return Command::SUCCESS;
    }
}
