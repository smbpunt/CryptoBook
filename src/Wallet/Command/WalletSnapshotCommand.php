<?php

namespace App\Wallet\Command;

use App\Wallet\Recorder\WalletSnapshotRecorder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'wallet:snapshot', description: 'Records the total value of every wallet for the current hour')]
final readonly class WalletSnapshotCommand
{
    public function __construct(private WalletSnapshotRecorder $recorder)
    {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $io->success(\sprintf('%d wallet snapshot(s) recorded.', $this->recorder->record()));

        return Command::SUCCESS;
    }
}
