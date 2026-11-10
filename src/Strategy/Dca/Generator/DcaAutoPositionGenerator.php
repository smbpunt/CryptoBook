<?php

namespace App\Strategy\Dca\Generator;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Position\Entity\Position;
use App\Strategy\Dca\Enum\DcaRecurrence;
use App\User\Entity\User;

/**
 * Spreads a total amount over recurring purchases of one coin. The quantities are left to fill in.
 */
final readonly class DcaAutoPositionGenerator
{
    /**
     * @return list<Position>
     */
    public function generate(User $user, Cryptocurrency $coin, DcaRecurrence $recurrence, int $count, float $totalUsd, \DateTimeImmutable $firstDate): array
    {
        $positions = [];
        $date = $firstDate;
        for ($i = 0; $i < $count; ++$i) {
            $positions[] = new Position($user)
                ->setOpenedAt($date)
                ->setCoin($coin)
                ->setEntryCost($totalUsd / $count)
                ->setIsOpened(true)
                ->setIsDca(true)
                ->setDescription('DCA Auto');
            $date = $date->add($recurrence->interval());
        }

        return $positions;
    }
}
