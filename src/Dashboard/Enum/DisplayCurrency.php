<?php

namespace App\Dashboard\Enum;

/**
 * Currency the dashboard is displayed in.
 */
enum DisplayCurrency: string
{
    case Favorite = 'favorite';
    case Usd = 'usd';
}
