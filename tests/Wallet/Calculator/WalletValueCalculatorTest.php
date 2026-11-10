<?php

namespace App\Tests\Wallet\Calculator;

use App\Position\Entity\Position;
use App\Tests\Fixtures\Dataset;
use App\Wallet\Calculator\WalletValueCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class WalletValueCalculatorTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private Dataset $data;
    private WalletValueCalculator $calculator;

    protected function setUp(): void
    {
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->data = Dataset::load($this->em);
        $this->calculator = self::getContainer()->get(WalletValueCalculator::class);
    }

    public function testValuesTheUserWallet(): void
    {
        // 0.5 BTC at 50 000 $ + 1 000 USDC at 1 $.
        $wallet = $this->calculator->forUser($this->data->user);

        self::assertSame(25000.0, $wallet->cryptoUsd);
        self::assertSame(1000.0, $wallet->stableUsd);
        self::assertSame(26000.0, $wallet->totalUsd);

        self::assertCount(1, $wallet->cryptoHoldings);
        $btc = $wallet->cryptoHoldings[0];
        self::assertSame($this->data->btc, $btc->coin);
        self::assertSame(0.5, $btc->quantity);
        self::assertSame(50000.0, $btc->priceUsd);
        self::assertSame(100.0, $btc->percent);

        self::assertCount(1, $wallet->stableHoldings);
        self::assertSame($this->data->usdc, $wallet->stableHoldings[0]->coin);
    }

    public function testSortsHoldingsByValueAndComputesShares(): void
    {
        // 1 ETH = 2 000 $, next to 25 000 $ of BTC.
        $this->em->persist(new Position($this->data->user)->setCoin($this->data->eth)->setNbCoins(1)->setEntryCost(1000));
        $this->em->flush();

        $holdings = $this->calculator->forUser($this->data->user)->cryptoHoldings;

        self::assertSame([$this->data->btc, $this->data->eth], array_map(static fn ($h) => $h->coin, $holdings));
        self::assertSame(92.59, $holdings[0]->percent);
        self::assertSame(7.41, $holdings[1]->percent);
    }

    public function testSumsOpenPositionsOfTheSameCoin(): void
    {
        $this->em->persist(new Position($this->data->user)->setCoin($this->data->btc)->setNbCoins(0.25)->setEntryCost(10000));
        $this->em->persist(new Position($this->data->user)->setCoin($this->data->btc)->setNbCoins(1)->setEntryCost(40000)->setIsOpened(false));
        $this->em->flush();

        $holdings = $this->calculator->forUser($this->data->user)->cryptoHoldings;

        self::assertCount(1, $holdings);
        self::assertSame(0.75, $holdings[0]->quantity);
        self::assertSame(37500.0, $holdings[0]->valueUsd);
    }

    public function testCoinsWithoutValueAreWorthZero(): void
    {
        // Open positions but nothing to share: no division by zero.
        $this->data->btc->setPriceUsd(0);
        $this->data->usdc->setPriceUsd(0);
        $this->em->flush();

        $wallet = $this->calculator->forUser($this->data->user);

        self::assertSame(0.0, $wallet->totalUsd);
        self::assertSame(0.0, $wallet->cryptoHoldings[0]->percent);
        self::assertSame(0.0, $wallet->stableHoldings[0]->percent);
    }

    public function testClosedPositionsCountForZero(): void
    {
        $this->data->position->setIsOpened(false);
        $this->em->flush();

        $wallet = $this->calculator->forUser($this->data->user);

        self::assertSame([], $wallet->cryptoHoldings);
        self::assertSame(1000.0, $wallet->totalUsd);
    }

    public function testValuesEveryOwnerWithAPosition(): void
    {
        $this->em->persist(new Position($this->data->admin)->setCoin($this->data->eth)->setNbCoins(1)->setEntryCost(1000));
        $this->em->flush();

        $totals = array_map(static fn ($wallet) => $wallet->totalUsd, $this->calculator->forAllUsers());
        ksort($totals);

        self::assertSame([$this->data->user->getId() => 26000.0, $this->data->admin->getId() => 2000.0], $totals);
    }
}
