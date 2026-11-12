<?php

namespace App\Dashboard\Cookie;

use App\Dashboard\Enum\DisplayCurrency;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Display currency of the dashboard, kept per device. The cookie only exists for USD: without it, the favorite currency.
 */
final readonly class DisplayCurrencyCookie
{
    public const string NAME = 'dashboard_currency';

    private const string LIFETIME = '+30 days';

    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    public function read(Request $request): DisplayCurrency
    {
        return DisplayCurrency::Usd->value === $request->cookies->get(self::NAME) ? DisplayCurrency::Usd : DisplayCurrency::Favorite;
    }

    public function write(Response $response, DisplayCurrency $currency): void
    {
        if (DisplayCurrency::Usd === $currency) {
            $response->headers->setCookie(Cookie::create(self::NAME, DisplayCurrency::Usd->value, $this->clock->now()->modify(self::LIFETIME)));

            return;
        }

        $response->headers->clearCookie(self::NAME);
    }

    /**
     * Each dashboard render in USD pushes the expiry back by 30 days.
     */
    public function extend(Response $response, DisplayCurrency $currency): void
    {
        if (DisplayCurrency::Usd === $currency) {
            $this->write($response, DisplayCurrency::Usd);
        }
    }
}
