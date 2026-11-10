<?php

namespace App\Tests\Position\Calculator;

use App\Position\Calculator\ExitPlanCalculator;
use App\Position\Entity\Position;
use App\Position\Entity\StrategyPosition;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class ExitPlanCalculatorTest extends TestCase
{
    public function testEachSaleSellsAShareOfTheCoinsLeft(): void
    {
        // 10 coins for 1 000 $: sell half at 150 $, then half of the rest at 300 $.
        $position = new Position(new User())->setNbCoins(10)->setEntryCost(1000)
            ->addStrategy(new StrategyPosition()->setPercent(50)->setPriceSold(150))
            ->addStrategy(new StrategyPosition()->setPercent(50)->setPriceSold(300));

        $plan = new ExitPlanCalculator()->calculate($position);

        self::assertCount(2, $plan->steps);
        self::assertSame(5.0, $plan->steps[0]->soldCoins);
        self::assertSame(750.0, $plan->steps[0]->recoveredUsd);
        self::assertSame(5.0, $plan->steps[0]->remainingCoins);
        self::assertSame(2.5, $plan->steps[1]->soldCoins);
        self::assertSame(750.0, $plan->steps[1]->recoveredUsd);
        self::assertSame(2.5, $plan->remainingCoins);
        self::assertSame(25.0, $plan->remainingPercent);
        self::assertSame(1500.0, $plan->recoveredUsd);
        self::assertSame(500.0, $plan->resultUsd);
        self::assertSame(50.0, $plan->resultPercent);
    }

    public function testResultPercentOfAFreePositionIsNull(): void
    {
        $position = new Position(new User())->setNbCoins(1)->setEntryCost(0)
            ->addStrategy(new StrategyPosition()->setPercent(100)->setPriceSold(10));

        $plan = new ExitPlanCalculator()->calculate($position);

        self::assertSame(10.0, $plan->resultUsd);
        self::assertNull($plan->resultPercent);
    }
}
