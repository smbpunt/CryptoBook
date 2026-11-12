<?php

namespace App\Tests\Deposit\Calculator;

use App\Deposit\Calculator\DepositTimelineCalculator;
use App\Deposit\Dto\DepositStep;
use App\Deposit\Entity\Deposit;
use App\FiatCurrency\Entity\FiatCurrency;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class DepositTimelineCalculatorTest extends TestCase
{
    private DepositTimelineCalculator $calculator;
    private FiatCurrency $eur;

    protected function setUp(): void
    {
        $this->calculator = new DepositTimelineCalculator();
        $this->eur = new FiatCurrency()->setFixerKey('EUR')->setRates(['USD' => 2.0]);
    }

    public function testCumulatesTheDepositsByDay(): void
    {
        $steps = $this->calculator->steps([
            $this->deposit(300, '2024-03-01 18:00'),
            $this->deposit(100, '2024-01-01'),
            $this->deposit(50, '2024-03-01 09:00'),
        ]);

        self::assertEquals([
            new DepositStep(new \DateTimeImmutable('2024-01-01 UTC'), 200.0),
            new DepositStep(new \DateTimeImmutable('2024-03-01 UTC'), 900.0),
        ], $steps);
        self::assertSame(0.0, $this->calculator->totalAt($steps, new \DateTimeImmutable('2023-12-31 UTC')));
        self::assertSame(200.0, $this->calculator->totalAt($steps, new \DateTimeImmutable('2024-02-15 UTC')));
        self::assertSame(900.0, $this->calculator->totalAt($steps, new \DateTimeImmutable('2024-03-01 00:00 UTC')));
    }

    public function testADepositWithoutDateCountsFromTheBeginning(): void
    {
        $steps = $this->calculator->steps([$this->deposit(100, '2024-01-01'), $this->deposit(10, null)]);

        self::assertEquals([
            new DepositStep(null, 20.0),
            new DepositStep(new \DateTimeImmutable('2024-01-01 UTC'), 220.0),
        ], $steps);
        self::assertSame(20.0, $this->calculator->totalAt($steps, new \DateTimeImmutable('2000-01-01 UTC')));
    }

    private function deposit(float $amount, ?string $depositedAt): Deposit
    {
        return new Deposit(new User())->setAmount($amount)->setFiatCurrency($this->eur)
            ->setDepositedAt(null === $depositedAt ? null : new \DateTimeImmutable($depositedAt.' UTC'));
    }
}
