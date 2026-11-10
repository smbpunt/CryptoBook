<?php

namespace App\Tests\Deposit\Calculator;

use App\Deposit\Calculator\DepositTotalCalculator;
use App\Deposit\Entity\Deposit;
use App\Entity\User;
use App\FiatCurrency\Entity\FiatCurrency;
use PHPUnit\Framework\TestCase;

final class DepositTotalCalculatorTest extends TestCase
{
    public function testTotalIsConvertedToUsd(): void
    {
        $user = new User();
        $usd = new FiatCurrency()->setFixerKey('USD');
        $eur = new FiatCurrency()->setFixerKey('EUR')->setRates(['USD' => 1.1]);

        $total = new DepositTotalCalculator()->totalUsd([
            new Deposit($user)->setAmount(100)->setFiatCurrency($eur),
            new Deposit($user)->setAmount(50)->setFiatCurrency($usd),
        ]);

        self::assertEqualsWithDelta(160.0, $total, 1e-9);
    }

    public function testTotalWithoutDepositIsZero(): void
    {
        self::assertSame(0.0, new DepositTotalCalculator()->totalUsd([]));
    }
}
