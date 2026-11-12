<?php

namespace App\Dashboard\Resolver;

use App\Dashboard\Cache\DashboardCache;
use App\Dashboard\Cookie\DisplayCurrencyCookie;
use App\Dashboard\Dto\DisplayedDashboard;
use App\Dashboard\Enum\DisplayCurrency;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Dashboard of a user in the currency chosen on this device. A user whose favorite currency is USD has nothing
 * to switch: the cookie is ignored.
 */
final readonly class DisplayedDashboardResolver
{
    public function __construct(
        private DashboardCache $dashboardCache,
        private DisplayCurrencyCookie $currencyCookie,
    ) {
    }

    public function resolve(Request $request, User $user): DisplayedDashboard
    {
        $favorite = $this->dashboardCache->get($user);

        if ($favorite->isUsd()) {
            return new DisplayedDashboard($favorite, DisplayCurrency::Favorite, null);
        }

        $display = $this->currencyCookie->read($request);

        return new DisplayedDashboard(
            DisplayCurrency::Usd === $display ? $favorite->inUsd() : $favorite,
            $display,
            $favorite->currencyCode,
        );
    }
}
