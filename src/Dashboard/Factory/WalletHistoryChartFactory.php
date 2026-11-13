<?php

namespace App\Dashboard\Factory;

use App\Dashboard\Dto\WalletHistory;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

/**
 * Chart.js view of the wallet history, in the favorite currency. Colors, formats and the tooltip are added by
 * assets/controllers/wallet_chart_controller.js: they read the CSS tokens and Intl, which PHP cannot serialize.
 *
 * The Y axis is fitted to the "Valeur" line: "Investi" is only drawn inside that range, and the tooltip reads it
 * on the points of "Valeur" (`invested` key).
 */
final readonly class WalletHistoryChartFactory
{
    /** Margin above and below the line, share of the range. */
    private const float MARGIN_RATIO = 0.05;
    /** Smallest range, share of the highest value: a flat line stays flat. */
    private const float MIN_SPAN_RATIO = 0.01;

    public function __construct(private ChartBuilderInterface $chartBuilder)
    {
    }

    public function create(WalletHistory $history, float $favoritePerUsd): Chart
    {
        if ($history->isEmpty()) {
            throw new \LogicException('An empty history has no chart.');
        }

        $hasDeposits = $history->hasDeposits();
        $values = [];
        // Linear X axis in milliseconds: no date adapter to load.
        foreach ($history->points as $point) {
            $value = [
                'x' => $point->at->getTimestamp() * 1000,
                'y' => round($point->valueUsd * $favoritePerUsd, 2),
            ];
            if ($hasDeposits) {
                $value['invested'] = round($point->investedUsd * $favoritePerUsd, 2);
            }
            $values[] = $value;
        }
        [$min, $max] = $this->valueRange(array_column($values, 'y'));

        $datasets = [
            [
                'label' => 'Valeur',
                'data' => $values,
                'fill' => 'start',
                'borderWidth' => 2.5,
                'tension' => 0.25,
                'pointRadius' => 0,
                'pointHoverRadius' => 5,
            ],
        ];
        // Out of the range, a null leaves a gap in the line: no point outside the frame, so no misplaced tooltip.
        $invested = [];
        if ($hasDeposits) {
            foreach ($values as $point) {
                $y = $point['invested'] ?? 0.0;
                $invested[] = ['x' => $point['x'], 'y' => $y >= $min && $y <= $max ? $y : null];
            }
        }
        if (self::drawsALine($invested)) {
            $datasets[] = [
                'label' => 'Investi',
                'data' => $invested,
                'stepped' => true,
                'borderDash' => [5, 5],
                'borderWidth' => 1.5,
                'pointRadius' => 0,
                'pointHoverRadius' => 0,
            ];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData(['datasets' => $datasets]);
        $chart->setOptions([
            'maintainAspectRatio' => false,
            'animation' => false,
            'interaction' => ['mode' => 'index', 'intersect' => false],
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'x' => [
                    'type' => 'linear',
                    'min' => $history->points[0]->at->getTimestamp() * 1000,
                    'max' => $history->points[\count($history->points) - 1]->at->getTimestamp() * 1000,
                    'grid' => ['display' => false],
                    'border' => ['display' => false],
                ],
                'y' => [
                    'min' => $min,
                    'max' => $max,
                    'border' => ['display' => false, 'dash' => [4, 4]],
                    // Round ticks inside the exact bounds, none on the bounds. Up to 8: with 5, a step rounded up (222 to 500)
                    // may leave a single tick inside the frame.
                    'ticks' => ['maxTicksLimit' => 8, 'includeBounds' => false],
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * Bounds of the Y axis: lowest and highest value, a minimal range centered on the line, a margin, never below 0.
     *
     * @param non-empty-list<float> $values
     *
     * @return array{float, float}
     */
    private function valueRange(array $values): array
    {
        $low = min($values);
        $high = max($values);
        $minSpan = max($high * self::MIN_SPAN_RATIO, 1.0);
        if ($high - $low < $minSpan) {
            $middle = ($low + $high) / 2;
            $low = $middle - $minSpan / 2;
            $high = $middle + $minSpan / 2;
        }
        $margin = ($high - $low) * self::MARGIN_RATIO;

        return [round(max(0.0, $low - $margin), 2), round($high + $margin, 2)];
    }

    /**
     * At least two consecutive points: a lone point draws nothing.
     *
     * @param list<array{x: int, y: ?float}> $points
     */
    private static function drawsALine(array $points): bool
    {
        return array_any(array_keys($points), static fn (int $i): bool => $i > 0 && null !== $points[$i]['y'] && null !== $points[$i - 1]['y']);
    }
}
