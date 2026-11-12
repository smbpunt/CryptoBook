<?php

namespace App\Dashboard\Controller;

use App\Dashboard\Cache\DashboardCache;
use App\Dashboard\Cookie\DisplayCurrencyCookie;
use App\Dashboard\Dto\DashboardData;
use App\Dashboard\Enum\DisplayCurrency;
use App\Dashboard\Factory\WalletHistoryChartFactory;
use App\Dashboard\Resolver\DisplayedDashboardResolver;
use App\User\Entity\User;
use App\Wallet\Enum\HistoryPeriod;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

#[Route('/dashboard')]
final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly DashboardCache $dashboardCache,
        private readonly WalletHistoryChartFactory $chartFactory,
        private readonly DisplayedDashboardResolver $displayedDashboardResolver,
        private readonly DisplayCurrencyCookie $currencyCookie,
    ) {
    }

    #[Route('', name: 'app_dashboard_index', methods: ['GET'])]
    public function index(Request $request, #[CurrentUser] User $user): Response
    {
        $displayed = $this->displayedDashboardResolver->resolve($request, $user);

        $response = $this->render('dashboard/index.html.twig', [
            'dashboard' => $displayed->dashboard,
            'displayCurrency' => $displayed->displayCurrency,
            'favoriteCode' => $displayed->favoriteCode,
            ...$this->historyVariables($user, $displayed->dashboard, HistoryPeriod::Month),
        ]);
        $this->currencyCookie->extend($response, $displayed->displayCurrency);

        return $response;
    }

    /**
     * The chart card alone, for a change of period in its Turbo Frame. Unknown period: 404. Outside the frame
     * (link opened in a new tab), the fragment would have no layout: back to the dashboard.
     */
    #[Route('/history/{period}', name: 'app_dashboard_history', methods: ['GET'])]
    public function history(HistoryPeriod $period, Request $request, #[CurrentUser] User $user): Response
    {
        if (!$request->headers->has('Turbo-Frame')) {
            return $this->redirectToRoute('app_dashboard_index');
        }

        $displayed = $this->displayedDashboardResolver->resolve($request, $user);

        $response = $this->render('dashboard/_history.html.twig', [
            'dashboard' => $displayed->dashboard,
            ...$this->historyVariables($user, $displayed->dashboard, $period),
        ]);
        $this->currencyCookie->extend($response, $displayed->displayCurrency);

        return $response;
    }

    /**
     * Keeps the display currency in a cookie, then back to the dashboard. Unknown currency: 404.
     */
    #[Route('/currency/{currency}', name: 'app_dashboard_currency', methods: ['POST'])]
    #[IsCsrfTokenValid('dashboard-currency')]
    public function currency(DisplayCurrency $currency): Response
    {
        $response = $this->redirectToRoute('app_dashboard_index', status: Response::HTTP_SEE_OTHER);
        $this->currencyCookie->write($response, $currency);

        return $response;
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
