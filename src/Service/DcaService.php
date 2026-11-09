<?php

namespace App\Service;

use App\Entity\Position;
use App\Entity\StrategyDca;
use App\Entity\User;
use App\Form\DcaAutoType;
use App\Repository\CryptocurrencyRepository;

class DcaService
{
    public function __construct(
        private readonly CryptocurrencyRepository $cryptocurrencyRepository,
    ) {
    }

    /**
     * @return Position[]
     */
    public function generatePositions(StrategyDca $dca, User $user, float $value): array
    {
        $positions = [];
        $today = \DateTimeImmutable::createFromFormat('Y-m-d H:i', date('Y-m-d H:i'));

        foreach ($dca->getParts() as $part) {
            $dollarInvested = $value * $part->getPercent() / 100;
            $coin = $part->getCoin();
            $nbCoinBought = round($dollarInvested / $coin->getPriceUsd(), 8);

            $position = new Position($user);
            $position->setOpenedAt($today)
                ->setCoin($coin)
                ->setEntryCost($dollarInvested)
                ->setNbCoins($nbCoinBought)
                ->setIsOpened(true)
                ->setIsDca(true)
                ->setDescription('DCA '.$part->getPercent().'%');
            $positions[] = $position;
        }

        return $positions;
    }

    /**
     * @return Position[]
     */
    public function generateDcaAutoPositions(User $user, int $coinId, string $recurrence, int $count, float $total, \DateTimeImmutable $firstDate): array
    {
        $coin = $this->cryptocurrencyRepository->find($coinId);
        if (null === $coin) {
            return [];
        }

        $modify = match ($recurrence) {
            DcaAutoType::HOURLY => '+4 hours',
            DcaAutoType::DAILY => '+1 day',
            DcaAutoType::WEEKLY => '+1 week',
            DcaAutoType::MONTHLY => '+1 month',
            default => null,
        };

        if (null === $modify) {
            return [];
        }

        $positions = [];
        $date = $firstDate;
        for ($i = 0; $i < $count; ++$i) {
            if ($i > 0) {
                $date = $date->modify($modify);
            }

            $position = new Position($user);
            $position->setOpenedAt($date)
                ->setCoin($coin)
                ->setEntryCost($total / $count)
                ->setIsOpened(true)
                ->setIsDca(true)
                ->setDescription('DCA Auto');

            $positions[] = $position;
        }

        return $positions;
    }
}
