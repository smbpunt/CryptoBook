<?php

namespace App\Fixer\Client;

use App\Fixer\Exception\FixerUnavailableException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fixer exchange rates API, through APILayer.
 */
final readonly class FixerClient
{
    public function __construct(
        #[Target('fixer.client')]
        private HttpClientInterface $fixerClient,
    ) {
    }

    /**
     * Latest rate of each symbol against the base currency. Unknown symbols are left out.
     *
     * @param list<string> $symbols
     *
     * @return array<string, float>
     *
     * @throws FixerUnavailableException
     */
    public function getLatestRates(string $base, array $symbols): array
    {
        try {
            $data = $this->fixerClient->request('GET', 'latest', [
                'query' => ['base' => $base, 'symbols' => implode(',', $symbols)],
            ])->toArray();
        } catch (HttpClientExceptionInterface $exception) {
            throw new FixerUnavailableException(\sprintf('Fixer call for base "%s" failed: %s', $base, $exception->getMessage()), previous: $exception);
        }

        if (!\is_array($data['rates'] ?? null)) {
            throw new FixerUnavailableException(\sprintf('Fixer answer for base "%s" has no rates.', $base));
        }

        $rates = [];
        foreach ($data['rates'] as $symbol => $rate) {
            if (\is_int($rate) || \is_float($rate)) {
                $rates[(string) $symbol] = (float) $rate;
            }
        }

        return $rates;
    }
}
