<?php

namespace App\Wallet\Command;

use App\Wallet\Recorder\WalletSnapshotRecorder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'wallet:snapshot', description: 'Records the current wallet value of every user')]
final readonly class WalletSnapshotCommand
{
    public function __construct(private WalletSnapshotRecorder $recorder)
    {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $count = $this->recorder->record();
        $io->success(\sprintf('Wallet value recorded for %d user(s).', $count));

        return Command::SUCCESS;
    }
}
