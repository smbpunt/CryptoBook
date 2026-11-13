<?php

namespace App\Tests\Dashboard\Factory;

use App\Dashboard\Dto\WalletHistory;
use App\Dashboard\Dto\WalletHistoryPoint;
use App\Dashboard\Factory\WalletHistoryChartFactory;
use App\Wallet\Enum\HistoryPeriod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\UX\Chartjs\Builder\ChartBuilder;
use Symfony\UX\Chartjs\Model\Chart;

final class WalletHistoryChartFactoryTest extends TestCase
{
    /**
     * @return iterable<string, array{list<float>, float, float}>
     */
    public static function ranges(): iterable
    {
        yield 'margin of 5 %' => [[24000, 30000, 26000], 23700.0, 30300.0];
        yield 'flat line, 1 % range' => [[25000, 25000], 24862.5, 25137.5];
        yield 'below the 1 % range' => [[25000, 25100], 24911.95, 25188.05];
        yield 'never below 0' => [[0, 1000], 0.0, 1050.0];
        yield 'empty wallet, range of 1' => [[0, 0], 0.0, 0.55];
    }

    /**
     * @param list<float> $values
     */
    #[DataProvider('ranges')]
    public function testYAxisFitsTheValue(array $values, float $min, float $max): void
    {
        $chart = $this->chart($values, array_fill(0, \count($values), 0.0));

        self::assertSame([$min, $max], self::yRange($chart));
    }

    public function testYAxisAndInvestedInTheFavoriteCurrency(): void
    {
        $chart = $this->chart([24000, 30000, 26000], [1000, 25000, 25000], 0.5);

        self::assertSame([11850.0, 15150.0], self::yRange($chart));
        self::assertSame([500.0, 12500.0, 12500.0], array_column(self::datasets($chart)[0]['data'], 'invested'));
        self::assertSame([null, 12500.0, 12500.0], self::ys($chart, 1));
    }

    public function testInvestedDrawnInsideTheRangeOnly(): void
    {
        $chart = $this->chart([24000, 30000, 26000], [1000, 25000, 25000]);

        self::assertCount(2, self::datasets($chart));
        self::assertSame([null, 25000.0, 25000.0], self::ys($chart, 1));
        self::assertSame([1000.0, 25000.0, 25000.0], array_column(self::datasets($chart)[0]['data'], 'invested'));
    }

    public function testInvestedOnTheBoundsIsDrawnAboveIsNot(): void
    {
        $chart = $this->chart([24000, 30000, 26000], [23700, 30300, 50000]);

        self::assertSame([23700.0, 30300.0, null], self::ys($chart, 1));
    }

    public function testInvestedOutOfTheRangeStaysInTheTooltip(): void
    {
        $chart = $this->chart([24000, 30000, 26000], [1000, 1000, 1000]);

        self::assertCount(1, self::datasets($chart));
        self::assertSame([1000.0, 1000.0, 1000.0], array_column(self::datasets($chart)[0]['data'], 'invested'));
    }

    public function testPointsInsideTheRangeButNotConsecutiveDrawNoLine(): void
    {
        $chart = $this->chart([24000, 30000, 26000], [25000, 1000, 25000]);

        self::assertCount(1, self::datasets($chart));
    }

    public function testNoInvestedWithoutDeposits(): void
    {
        // An axis from 0: a line of invested at 0 would be inside it.
        $chart = $this->chart([0, 1000, 500], [0, 0, 0]);

        self::assertCount(1, self::datasets($chart));
        foreach (self::datasets($chart)[0]['data'] as $point) {
            self::assertArrayNotHasKey('invested', $point);
        }
    }

    /**
     * One point a day, in USD.
     *
     * @param list<float> $values
     * @param list<float> $invested
     */
    private function chart(array $values, array $invested, float $favoritePerUsd = 1.0): Chart
    {
        $start = new \DateTimeImmutable('2026-11-01 00:00 UTC');
        $points = array_map(
            static fn (int $day, float $value, float $investedUsd): WalletHistoryPoint => new WalletHistoryPoint($start->modify("+{$day} days"), $value, $investedUsd),
            array_keys($values),
            $values,
            $invested,
        );

        return new WalletHistoryChartFactory(new ChartBuilder())->create(new WalletHistory(HistoryPeriod::Month, $points), $favoritePerUsd);
    }

    /**
     * @return list<array{data: list<array{x: int, y: ?float, invested?: float}>}>
     */
    private static function datasets(Chart $chart): array
    {
        /** @var list<array{data: list<array{x: int, y: ?float, invested?: float}>}> $datasets */
        $datasets = $chart->getData()['datasets'];

        return $datasets;
    }

    /**
     * @return list<?float>
     */
    private static function ys(Chart $chart, int $dataset): array
    {
        return array_column(self::datasets($chart)[$dataset]['data'], 'y');
    }

    /**
     * @return array{mixed, mixed}
     */
    private static function yRange(Chart $chart): array
    {
        $y = $chart->getOptions()['scales']['y'];

        return [$y['min'], $y['max']];
    }
}
