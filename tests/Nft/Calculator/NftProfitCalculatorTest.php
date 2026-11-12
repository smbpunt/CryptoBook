<?php

namespace App\Tests\Nft\Calculator;

use App\Nft\Calculator\NftProfitCalculator;
use App\Nft\Entity\Nft;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class NftProfitCalculatorTest extends TestCase
{
    public function testSumsTheProfitOfTheNfts(): void
    {
        $user = new User();
        $profit = new NftProfitCalculator()->totalUsd([
            new Nft($user)->setPriceUsd(2000)->setPriceSoldUsd(3000)->setPercentSaleFees(10)->setSoldOn(new \DateTimeImmutable()),
            new Nft($user)->setPriceUsd(1000)->setPriceSoldUsd(500)->setSoldOn(new \DateTimeImmutable()),
        ]);

        self::assertEqualsWithDelta(200.0, $profit, 1e-9);
    }

    public function testNoNftIsZero(): void
    {
        self::assertSame(0.0, new NftProfitCalculator()->totalUsd([]));
    }
}
