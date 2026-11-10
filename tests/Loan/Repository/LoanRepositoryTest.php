<?php

namespace App\Tests\Loan\Repository;

use App\Loan\Repository\LoanRepository;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class LoanRepositoryTest extends KernelTestCase
{
    public function testSumValueUsd(): void
    {
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $repository = self::getContainer()->get(LoanRepository::class);

        // 500 USDC at 1 $.
        self::assertSame(500.0, $repository->sumValueUsd($data->user));
        self::assertSame(0.0, $repository->sumValueUsd($data->admin));
    }
}
