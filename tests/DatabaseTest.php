<?php

namespace App\Tests;

use App\Entity\FiatCurrency;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Checks the test setup: the kernel boots on the test database and DAMA rolls back each test.
 */
final class DatabaseTest extends KernelTestCase
{
    public function testWriteToDatabase(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $em->persist(new FiatCurrency()->setLibelle('Test')->setFixerKey('TST')->setSymbol('T'));
        $em->flush();

        self::assertSame(1, $this->countTestCurrencies());
    }

    #[Depends('testWriteToDatabase')]
    public function testPreviousWriteIsRolledBack(): void
    {
        self::assertSame(0, $this->countTestCurrencies());
    }

    private function countTestCurrencies(): int
    {
        return self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(FiatCurrency::class)
            ->count(['fixerKey' => 'TST']);
    }
}
