<?php

namespace App\Tests\FiatCurrency\Entity;

use App\Deposit\Entity\Deposit;
use App\Entity\User;
use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Exception\MissingRateException;
use PHPUnit\Framework\TestCase;

final class FiatCurrencyTest extends TestCase
{
    public function testRateTo(): void
    {
        $eur = new FiatCurrency()->setFixerKey('EUR')->setRates(['USD' => 1.1]);

        self::assertSame(1.1, $eur->rateTo('USD'));
        self::assertSame(1.0, $eur->rateTo('EUR'));
    }

    public function testMissingRateIsAnError(): void
    {
        $this->expectException(MissingRateException::class);
        $this->expectExceptionMessage('No EUR to AUD rate');

        new FiatCurrency()->setFixerKey('EUR')->setRates(['USD' => 1.1])->rateTo('AUD');
    }

    public function testDepositInACurrencyWithoutRateIsAnError(): void
    {
        $deposit = new Deposit(new User())->setAmount(100)->setFiatCurrency(new FiatCurrency()->setFixerKey('AUD'));

        $this->expectException(MissingRateException::class);

        $deposit->getAmountUsd();
    }
}
