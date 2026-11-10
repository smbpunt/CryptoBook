<?php

namespace App\Tests\Unit\Service;

use App\Entity\Deposit;
use App\Entity\FiatCurrency;
use App\Entity\User;
use App\Repository\DepositRepository;
use App\Service\DepositService;
use PHPUnit\Framework\TestCase;

final class DepositServiceTest extends TestCase
{
    public function testTotalDepositOfTheUserIsConvertedToUsd(): void
    {
        $user = new User();
        $usd = new FiatCurrency()->setFixerKey('USD');
        $eur = new FiatCurrency()->setFixerKey('EUR')->setRates(['USD' => 1.1]);

        $repository = $this->createMock(DepositRepository::class);
        $repository->expects(self::once())->method('findByOwnerWithCurrency')->with($user)->willReturn([
            new Deposit($user)->setAmount(100)->setFiatCurrency($eur),
            new Deposit($user)->setAmount(50)->setFiatCurrency($usd),
        ]);
        $total = new DepositService($repository)->getTotalDepositUsd($user);

        self::assertEqualsWithDelta(160.0, $total, 1e-9);
    }
}
