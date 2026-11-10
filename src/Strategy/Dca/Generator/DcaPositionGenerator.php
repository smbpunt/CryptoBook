<?php

namespace App\Strategy\Dca\Generator;

use App\Position\Entity\Position;
use App\Strategy\Dca\Entity\StrategyDca;
use App\User\Entity\User;
use Psr\Clock\ClockInterface;

/**
 * Splits an amount between the coins of the DCA, at their current price.
 */
final readonly class DcaPositionGenerator
{
    public function __construct(private ClockInterface $clock)
    {
    }

    /**
     * @return list<Position>
     */
    public function generate(StrategyDca $dca, User $user, float $amountUsd): array
    {
        $now = $this->clock->now();
        $openedAt = $now->setTime((int) $now->format('H'), (int) $now->format('i'));

        $positions = [];
        foreach ($dca->getParts() as $part) {
            $invested = $amountUsd * $part->getPercent() / 100;
            $coin = $part->getCoin();

            $positions[] = new Position($user)
                ->setOpenedAt($openedAt)
                ->setCoin($coin)
                ->setEntryCost($invested)
                ->setNbCoins(round($invested / $coin->getPriceUsd(), 8))
                ->setIsOpened(true)
                ->setIsDca(true)
                ->setDescription('DCA '.$part->getPercent().'%');
        }

        return $positions;
    }
}
