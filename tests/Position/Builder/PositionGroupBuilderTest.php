<?php

namespace App\Tests\Position\Builder;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Position\Builder\PositionGroupBuilder;
use App\Position\Entity\Position;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class PositionGroupBuilderTest extends TestCase
{
    public function testGroupsByCoinWithTheWeightedAverageCost(): void
    {
        $btc = $this->coin(1, 50000);
        $eth = $this->coin(2, 2000);
        $first = $this->position($btc, 0.5, 20000);
        $second = $this->position($eth, 2, 3000);
        $third = $this->position($btc, 1.5, 90000);

        $groups = new PositionGroupBuilder()->build([$first, $second, $third]);

        self::assertCount(2, $groups);
        self::assertSame($btc, $groups[0]->coin);
        self::assertSame([$first, $third], $groups[0]->positions);
        self::assertSame(2.0, $groups[0]->quantity);
        // 0.5 BTC at 40 000 $ and 1.5 BTC at 60 000 $.
        self::assertEqualsWithDelta(55000.0, $groups[0]->averageCost, 1e-9);
        self::assertSame(100000.0, $groups[0]->valueUsd());
        self::assertEqualsWithDelta(-9.0909, $groups[0]->evolutionPercent(), 1e-4);
        self::assertSame($eth, $groups[1]->coin);
        self::assertEqualsWithDelta(1500.0, $groups[1]->averageCost, 1e-9);
    }

    public function testAverageCostOfACoinUnderOneCent(): void
    {
        $group = new PositionGroupBuilder()->build([$this->position($this->coin(1, 0.00002), 1_000_000, 10)])[0];

        self::assertEqualsWithDelta(0.00001, $group->averageCost, 1e-12);
        self::assertEqualsWithDelta(100.0, $group->evolutionPercent(), 1e-9);
    }

    public function testNoPosition(): void
    {
        self::assertSame([], new PositionGroupBuilder()->build([]));
    }

    private function coin(int $id, float $price): Cryptocurrency
    {
        $coin = new Cryptocurrency()->setPriceUsd($price);
        new \ReflectionProperty(Cryptocurrency::class, 'id')->setValue($coin, $id);

        return $coin;
    }

    private function position(Cryptocurrency $coin, float $nbCoins, float $entryCost): Position
    {
        $position = new Position(new User())->setCoin($coin)->setNbCoins($nbCoins)->setEntryCost($entryCost);
        $position->calculateRemainingCoins();

        return $position;
    }
}
