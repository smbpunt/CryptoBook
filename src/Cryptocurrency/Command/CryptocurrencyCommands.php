<?php

namespace App\Cryptocurrency\Command;

use App\Service\CryptocurrencyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Refreshes the coin catalogue from CoinGecko.
 */
final readonly class CryptocurrencyCommands
{
    public function __construct(private CryptocurrencyService $cryptocurrencyService)
    {
    }

    #[AsCommand(name: 'crypto:updatePrices', description: 'Updates the USD price of every coin from CoinGecko')]
    public function updatePrices(SymfonyStyle $io): int
    {
        $this->cryptocurrencyService->updatePrices();
        $io->success('Prices updated from CoinGecko.');

        return Command::SUCCESS;
    }

    #[AsCommand(name: 'crypto:updateDatas', description: 'Updates name, symbol, price, market cap and images of every coin from CoinGecko')]
    public function updateDatas(SymfonyStyle $io): int
    {
        $this->cryptocurrencyService->updateAllCryptos();
        $io->success('Coins updated from CoinGecko.');

        return Command::SUCCESS;
    }
}
