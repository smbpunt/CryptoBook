<?php

namespace App\Dashboard\Controller;

use App\Dashboard\Cache\DashboardCache;
use App\Dashboard\Dto\DashboardData;
use App\Dashboard\Factory\WalletHistoryChartFactory;
use App\User\Entity\User;
use App\Wallet\Enum\HistoryPeriod;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly DashboardCache $dashboardCache,
        private readonly WalletHistoryChartFactory $chartFactory,
    ) {
    }

    #[Route('/', name: 'home')]
    public function index(#[CurrentUser] User $user): Response
    {
        $dashboard = $this->dashboardCache->get($user);

        return $this->render('dashboard/index.html.twig', [
            'dashboard' => $dashboard,
            ...$this->historyVariables($user, $dashboard, HistoryPeriod::Month),
        ]);
    }

    /**
     * The chart card alone, for a change of period in its Turbo Frame. Unknown period: 404. Outside the frame
     * (link opened in a new tab), the fragment would have no layout: back to the dashboard.
     */
    #[Route('/history/{period}', name: 'app_dashboard_history', methods: ['GET'])]
    public function history(HistoryPeriod $period, Request $request, #[CurrentUser] User $user): Response
    {
        if (!$request->headers->has('Turbo-Frame')) {
            return $this->redirectToRoute('home');
        }

        $dashboard = $this->dashboardCache->get($user);

        return $this->render('dashboard/_history.html.twig', [
            'dashboard' => $dashboard,
            ...$this->historyVariables($user, $dashboard, $period),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function historyVariables(User $user, DashboardData $dashboard, HistoryPeriod $period): array
    {
        $history = $this->dashboardCache->getHistory($user, $period);

        return [
            'history' => $history,
            'chart' => $history->isEmpty() ? null : $this->chartFactory->create($history, $dashboard->favoritePerUsd),
            'periods' => HistoryPeriod::cases(),
        ];
    }
}
