<?php

namespace App\Dashboard\Dto;

use App\Dashboard\Enum\DisplayCurrency;

/**
 * The dashboard in the currency it is displayed in.
 */
final readonly class DisplayedDashboard
{
    public function __construct(
        public DashboardData $dashboard,
        public DisplayCurrency $displayCurrency,
        /** ISO code of the favorite currency, null when it is USD: nothing to switch. */
        public ?string $favoriteCode,
    ) {
    }
}
