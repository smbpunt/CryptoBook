<?php

namespace App\Coingecko\Exception;

/**
 * CoinGecko unreachable, rate limited, failing or answering something unreadable.
 */
final class CoingeckoUnavailableException extends \RuntimeException
{
}
