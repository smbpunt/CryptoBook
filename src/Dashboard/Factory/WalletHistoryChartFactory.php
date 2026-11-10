<?php

namespace App\Dashboard\Factory;

use App\Dashboard\Dto\WalletHistory;
use App\Dashboard\Dto\WalletHistoryPoint;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

/**
 * Chart.js view of the wallet history, in the favorite currency. Colors, formats and the tooltip are added by
 * assets/controllers/wallet_chart_controller.js: they read the CSS tokens and Intl, which PHP cannot serialize.
 */
final readonly class WalletHistoryChartFactory
{
    public function __construct(private ChartBuilderInterface $chartBuilder)
    {
    }

    public function create(WalletHistory $history, float $favoritePerUsd): Chart
    {
        if ($history->isEmpty()) {
            throw new \LogicException('An empty history has no chart.');
        }

        // Linear X axis in milliseconds: no date adapter to load.
        $series = static fn (callable $usd): array => array_map(static fn (WalletHistoryPoint $point): array => [
            'x' => $point->at->getTimestamp() * 1000,
            'y' => round($usd($point) * $favoritePerUsd, 2),
        ], $history->points);

        $datasets = [
            [
                'label' => 'Valeur',
                'data' => $series(static fn (WalletHistoryPoint $point): float => $point->valueUsd),
                'fill' => 'start',
                'borderWidth' => 2.5,
                'tension' => 0.25,
                'pointRadius' => 0,
                'pointHoverRadius' => 5,
            ],
        ];
        // Without deposits, a line at 0 would only stretch the Y axis.
        if ($history->hasDeposits()) {
            $datasets[] = [
                'label' => 'Investi',
                'data' => $series(static fn (WalletHistoryPoint $point): float => $point->investedUsd),
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
                    'grace' => '10%',
                    'border' => ['display' => false, 'dash' => [4, 4]],
                    'ticks' => ['maxTicksLimit' => 5],
                ],
            ],
        ]);

        return $chart;
    }
}
