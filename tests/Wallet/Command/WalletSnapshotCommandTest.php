<?php

namespace App\Tests\Wallet\Command;

use App\Tests\Fixtures\Dataset;
use App\Wallet\Repository\WalletSnapshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class WalletSnapshotCommandTest extends KernelTestCase
{
    public function testRecordsTheSnapshots(): void
    {
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));

        $result = self::runCommand('wallet:snapshot');

        self::assertCommandIsSuccessful($result);
        self::assertStringContainsString('1 wallet snapshot(s) recorded.', $result->getDisplay());
        $snapshot = self::getContainer()->get(WalletSnapshotRepository::class)->findOneBy(['owner' => $data->user]);
        self::assertSame(26000.0, $snapshot?->getValueUsd());
    }
}
