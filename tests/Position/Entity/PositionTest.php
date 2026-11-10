<?php

namespace App\Tests\Position\Entity;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Position\Entity\Position;
use App\Position\Entity\Vente;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class PositionTest extends TestCase
{
    public function testRemainingCoinsWithoutSales(): void
    {
        $position = $this->position(nbCoins: 2, entryCost: 100);

        $position->calculateRemainingCoins();

        self::assertSame(2.0, $position->getRemainingCoins());
        self::assertTrue($position->isIsOpened());
    }

    public function testSalesCompoundOnRemainingCoins(): void
    {
        $position = $this->position(nbCoins: 2, entryCost: 100);
        $position->addVente($this->vente('2024-02-01', 50));
        $position->addVente($this->vente('2024-01-01', 50));

        $position->calculateRemainingCoins();

        self::assertSame(0.5, $position->getRemainingCoins());
        self::assertSame(0.25, $position->getPercentRemainingCoins());
        self::assertTrue($position->isIsOpened());
    }

    public function testFullSaleClosesPosition(): void
    {
        $position = $this->position(nbCoins: 2, entryCost: 100);
        $position->addVente($this->vente('2024-01-01', 100));

        $position->calculateRemainingCoins();

        self::assertSame(0.0, $position->getRemainingCoins());
        self::assertFalse($position->isIsOpened());
    }

    public function testValuations(): void
    {
        $position = $this->position(nbCoins: 4, entryCost: 1000, priceUsd: 300);
        $position->addVente($this->vente('2024-01-01', 50));
        $position->calculateRemainingCoins();

        self::assertSame(250.0, $position->getEntryCoinValue());
        self::assertSame(600.0, $position->getCurrentValue());
        // Remaining half cost 500, now worth 600.
        self::assertEqualsWithDelta(20.0, $position->getPercentEvolution(), 1e-9);
    }

    public function testEntryCoinValueIsRoundedToCents(): void
    {
        self::assertSame(333.33, $this->position(nbCoins: 3, entryCost: 1000)->getEntryCoinValue());
    }

    public function testPercentEvolutionFallbackWhenEntryCostIsZero(): void
    {
        $position = $this->position(nbCoins: 1, entryCost: 0);
        $position->calculateRemainingCoins();

        self::assertSame(9999.0, (float) $position->getPercentEvolution());
    }

    private function position(float $nbCoins, float $entryCost, float $priceUsd = 1): Position
    {
        return new Position(new User())
            ->setCoin(new Cryptocurrency()->setPriceUsd($priceUsd))
            ->setNbCoins($nbCoins)
            ->setEntryCost($entryCost);
    }

    private function vente(string $date, float $percent): Vente
    {
        return new Vente()->setSoldAt(new \DateTimeImmutable($date))->setPercent($percent)->setPriceSold(0);
    }
}
