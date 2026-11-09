<?php

namespace App\Coingecko\Client;

use App\Coingecko\Dto\Coin;
use App\Coingecko\Exception\CoingeckoUnavailableException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * CoinGecko public API (v3).
 */
final readonly class CoingeckoClient
{
    public function __construct(
        #[Target('coingecko.client')]
        private HttpClientInterface $coingeckoClient,
    ) {
    }

    /**
     * @throws CoingeckoUnavailableException
     */
    public function getCoin(string $id): Coin
    {
        $data = $this->get('coins/'.rawurlencode($id), [
            'localization' => 'false',
            'tickers' => 'false',
            'community_data' => 'false',
            'developer_data' => 'false',
        ]);

        return new Coin(
            name: self::string($data['name'] ?? null),
            symbol: self::string($data['symbol'] ?? null),
            priceUsd: self::float($data['market_data']['current_price']['usd'] ?? null),
            marketCapUsd: self::float($data['market_data']['market_cap']['usd'] ?? null),
            imageThumb: self::string($data['image']['thumb'] ?? null),
            imageSmall: self::string($data['image']['small'] ?? null),
            imageLarge: self::string($data['image']['large'] ?? null),
        );
    }

    /**
     * USD price of each coin, by CoinGecko id. Unknown coins are left out.
     *
     * @param list<string> $ids
     *
     * @return array<string, float>
     *
     * @throws CoingeckoUnavailableException
     */
    public function getUsdPrices(array $ids): array
    {
        if ([] === $ids) {
            return [];
        }

        $data = $this->get('simple/price', ['ids' => implode(',', $ids), 'vs_currencies' => 'usd']);

        $prices = [];
        foreach ($data as $id => $price) {
            $usd = self::float(\is_array($price) ? ($price['usd'] ?? null) : null);
            if (null !== $usd) {
                $prices[(string) $id] = $usd;
            }
        }

        return $prices;
    }

    /**
     * @param array<string, string> $query
     *
     * @return array<mixed>
     *
     * @throws CoingeckoUnavailableException
     */
    private function get(string $path, array $query): array
    {
        try {
            return $this->coingeckoClient->request('GET', $path, ['query' => $query])->toArray();
        } catch (HttpClientExceptionInterface $exception) {
            throw new CoingeckoUnavailableException(\sprintf('CoinGecko call to "%s" failed: %s', $path, $exception->getMessage()), previous: $exception);
        }
    }

    private static function string(mixed $value): ?string
    {
        return \is_string($value) && '' !== $value ? $value : null;
    }

    private static function float(mixed $value): ?float
    {
        return \is_int($value) || \is_float($value) ? (float) $value : null;
    }
}
