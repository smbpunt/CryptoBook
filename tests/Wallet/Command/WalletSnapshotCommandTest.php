<?php

namespace App\Tests\Wallet\Command;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class WalletSnapshotCommandTest extends KernelTestCase
{
    public function testRecordsTheSnapshots(): void
    {
        Dataset::load(self::getContainer()->get(EntityManagerInterface::class));

        $result = self::runCommand('wallet:snapshot');

        self::assertCommandIsSuccessful($result);
        self::assertStringContainsString('1 wallet snapshot(s) recorded.', $result->getDisplay());
    }
}
