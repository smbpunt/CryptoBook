<?php

namespace App\Tests\Wallet\Calculator;

use App\Entity\Position;
use App\Tests\Fixtures\Dataset;
use App\Wallet\Calculator\WalletValueCalculator;
use App\Wallet\Dto\Holding;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class WalletValueCalculatorTest extends KernelTestCase
{
    public function testValuesOpenPositionsAtCurrentPrices(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $d = Dataset::load($em);

        // Dataset: 0.5 BTC (50 000 $) and 1 000 USDC (1 $). ETH ends up worth more than BTC.
        $em->persist(new Position($d->user)->setCoin($d->btc)->setNbCoins(0.25)->setEntryCost(10000));
        $em->persist(new Position($d->user)->setCoin($d->eth)->setNbCoins(25)->setEntryCost(5000));
        $em->persist(new Position($d->user)->setCoin($d->eth)->setNbCoins(100)->setEntryCost(1)->setIsOpened(false));
        $em->persist(new Position($d->admin)->setCoin($d->btc)->setNbCoins(10)->setEntryCost(1));
        $em->flush();

        $wallet = self::getContainer()->get(WalletValueCalculator::class)->calculate($d->user);

        self::assertSame(['eth', 'btc'], array_map(static fn (Holding $h): string => $h->symbol, $wallet->cryptoHoldings));
        [$eth, $btc] = $wallet->cryptoHoldings;
        self::assertEqualsWithDelta(0.75, $btc->quantity, 1e-9);
        self::assertEqualsWithDelta(37500.0, $btc->valueUsd, 1e-9);
        self::assertEqualsWithDelta(50000.0, $eth->valueUsd, 1e-9);
        self::assertSame(42.86, $btc->percent);
        self::assertSame(57.14, $eth->percent);

        self::assertCount(1, $wallet->stableHoldings);
        self::assertSame(100.0, $wallet->stableHoldings[0]->percent);

        self::assertEqualsWithDelta(87500.0, $wallet->cryptoUsd, 1e-9);
        self::assertEqualsWithDelta(1000.0, $wallet->stableUsd, 1e-9);
        self::assertEqualsWithDelta(88500.0, $wallet->totalUsd(), 1e-9);
    }

    public function testHoldingsWorthNothingDoNotDivideByZero(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $d = Dataset::load($em);
        $d->usdc->setPriceUsd(0);
        $em->flush();

        $wallet = self::getContainer()->get(WalletValueCalculator::class)->calculate($d->user);

        self::assertCount(1, $wallet->stableHoldings);
        self::assertSame(0.0, $wallet->stableHoldings[0]->percent);
        self::assertSame(0.0, $wallet->stableUsd);
    }
}
