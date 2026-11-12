<?php

namespace App\Wallet\Command;

use App\Coingecko\Exception\CoingeckoUnavailableException;
use App\Wallet\Backfiller\WalletHistoryBackfiller;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'wallet:backfill', description: 'Fills the last 30 days of wallet history from the current wallets and the CoinGecko hourly prices')]
final readonly class WalletBackfillCommand
{
    public function __construct(private WalletHistoryBackfiller $backfiller)
    {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        try {
            $report = $this->backfiller->backfill();
        } catch (CoingeckoUnavailableException $exception) {
            $io->error('Nothing written: '.$exception->getMessage());

            return Command::FAILURE;
        }

        $io->success(\sprintf('%d snapshot(s) backfilled for %d user(s), %d coin(s) fetched.', $report->snapshots, $report->users, $report->fetchedCoins));

        return Command::SUCCESS;
    }
}
