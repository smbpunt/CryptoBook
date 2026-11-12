<?php

namespace App\Tests\Dashboard\Dto;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Dashboard\Dto\DashboardHolding;
use App\Wallet\Dto\WalletHolding;
use PHPUnit\Framework\TestCase;

final class DashboardHoldingTest extends TestCase
{
    public function testKeepsTheHexColorOfTheAdmin(): void
    {
        self::assertSame('#F7931A', $this->holding('btc', '#F7931A')->color);
    }

    public function testDerivesTheColorFromTheSymbolWithoutColor(): void
    {
        self::assertSame('hsl(68 65% 55%)', $this->holding('btc', null)->color);
        self::assertSame('hsl(68 65% 55%)', $this->holding('BTC', null)->color);
    }

    public function testReplacesAColorThatIsNotHex(): void
    {
        self::assertSame(DashboardHolding::colorFromSymbol('btc'), $this->holding('btc', 'red; background: url(x)')->color);
        self::assertSame(DashboardHolding::colorFromSymbol('btc'), $this->holding('btc', '#F7931A; background: url(x)')->color);
    }

    private function holding(string $symbol, ?string $color): DashboardHolding
    {
        $coin = new Cryptocurrency()
            ->setLibelle('Bitcoin')
            ->setSymbol($symbol)
            ->setColor($color);

        return DashboardHolding::fromWalletHolding(new WalletHolding($coin, 1, 1, 1, 100));
    }
}
