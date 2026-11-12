<?php

namespace App\Wallet\Backfiller;

use App\Coingecko\Client\CoingeckoClient;
use App\Coingecko\Exception\CoingeckoUnavailableException;
use App\User\Entity\User;
use App\Wallet\Calculator\WalletValueCalculator;
use App\Wallet\Dto\BackfillReport;
use App\Wallet\Dto\WalletHolding;
use App\Wallet\Entity\WalletSnapshot;
use App\Wallet\Repository\WalletSnapshotRepository;
use App\Wallet\ValueObject\HourlyPriceSeries;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;

/**
 * Fills the hours of the last 30 days before the first snapshot of each user, from the current wallet and the
 * CoinGecko hourly prices. Coins worth less than 5 $ for every holder keep their current price, like stablecoins.
 */
final readonly class WalletHistoryBackfiller
{
    private const int DAYS = 30;
    private const float MIN_VALUE_USD = 5.0;

    public function __construct(
        private WalletValueCalculator $calculator,
        private WalletSnapshotRepository $snapshotRepository,
        private CoingeckoClient $coingeckoClient,
        private EntityManagerInterface $em,
        private ClockInterface $clock,
    ) {
    }

    /**
     * Every price is fetched before anything is written: a CoinGecko failure leaves the history untouched.
     *
     * @throws CoingeckoUnavailableException
     */
    public function backfill(): BackfillReport
    {
        $now = $this->clock->now()->setTimezone(new \DateTimeZone('UTC'));
        $currentHour = $now->setTime((int) $now->format('G'), 0);
        $start = $currentHour->modify(\sprintf('-%d hours', self::DAYS * 24));
        $firstRecordedAt = $this->snapshotRepository->findFirstRecordedAtByOwner();

        /** @var array<int, array{list<WalletHolding>, \DateTimeImmutable}> $wallets holdings and end (excluded) by owner id */
        $wallets = [];
        foreach ($this->calculator->forAllUsers() as $ownerId => $valuation) {
            $end = $firstRecordedAt[$ownerId] ?? $currentHour;
            if ($valuation->totalUsd <= 0 || $end <= $start) {
                continue;
            }
            $wallets[$ownerId] = [[...$valuation->cryptoHoldings, ...$valuation->stableHoldings], $end];
        }

        $series = $this->fetchSeries($wallets);

        $snapshots = 0;
        foreach ($wallets as $ownerId => [$holdings, $end]) {
            $owner = $this->em->getReference(User::class, $ownerId);
            for ($hour = $start; $hour < $end; $hour = $hour->modify('+1 hour')) {
                $this->em->persist(new WalletSnapshot($owner, $hour, self::valueAt($holdings, $series, $hour)));
                ++$snapshots;
            }
        }
        $this->em->flush();

        return new BackfillReport(\count($wallets), $snapshots, \count($series));
    }

    /**
     * One call per crypto (never a stablecoin) worth at least 5 $ for one of its holders.
     *
     * @param array<int, array{list<WalletHolding>, \DateTimeImmutable}> $wallets
     *
     * @return array<string, HourlyPriceSeries> by CoinGecko id
     *
     * @throws CoingeckoUnavailableException
     */
    private function fetchSeries(array $wallets): array
    {
        $series = [];
        foreach ($wallets as [$holdings]) {
            foreach ($holdings as $holding) {
                $id = $holding->coin->getLibelleCoingecko();
                if (null === $id || isset($series[$id]) || $holding->coin->isIsStable() || $holding->valueUsd < self::MIN_VALUE_USD) {
                    continue;
                }
                $series[$id] = HourlyPriceSeries::fromPoints($this->coingeckoClient->getHourlyUsdPrices($id, self::DAYS));
            }
        }

        return $series;
    }

    /**
     * @param list<WalletHolding>              $holdings
     * @param array<string, HourlyPriceSeries> $series
     */
    private static function valueAt(array $holdings, array $series, \DateTimeImmutable $hour): float
    {
        $valueUsd = 0.0;
        foreach ($holdings as $holding) {
            $id = $holding->coin->getLibelleCoingecko();
            $price = null !== $id && isset($series[$id]) ? $series[$id]->at($hour) : null;
            $valueUsd += null !== $price ? $holding->quantity * $price : $holding->valueUsd;
        }

        return $valueUsd;
    }
}
