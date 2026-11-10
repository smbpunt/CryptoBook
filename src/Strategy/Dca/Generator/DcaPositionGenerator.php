<?php

namespace App\Strategy\Dca\Generator;

use App\Position\Entity\Position;
use App\Strategy\Dca\Entity\StrategyDca;
use App\User\Entity\User;
use Psr\Clock\ClockInterface;

/**
 * Splits an amount between the coins of the DCA, at their current price. A part at 0 % buys nothing.
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
            if ($part->getPercent() <= 0) {
                continue;
            }

            $invested = $amountUsd * $part->getPercent() / 100;
            $coin = $part->getCoin();

            $positions[] = new Position($user)
                ->setOpenedAt($openedAt)
                ->setCoin($coin)
                ->setEntryCost($invested)
                // Without a price, the quantity is left to fill in.
                ->setNbCoins($coin->getPriceUsd() > 0 ? round($invested / $coin->getPriceUsd(), 8) : 0.0)
                ->setIsOpened(true)
                ->setIsDca(true)
                ->setDescription('DCA '.$part->getPercent().'%');
        }

        return $positions;
    }
}
