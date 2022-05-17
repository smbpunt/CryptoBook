<?php

namespace App\Service;

use App\Entity\Position;
use App\Entity\StrategyDca;
use DateTimeImmutable;
use Symfony\Component\Security\Core\User\UserInterface;

class DcaService
{
    /**
     * @param StrategyDca $dca
     * @param UserInterface $user
     * @param float $value
     * @return Position[]
     */
    public function generatePositions(StrategyDca $dca, UserInterface $user, float $value): array
    {
        $positions = [];
        $today = new DateTimeImmutable();

        foreach ($dca->getParts() as $part) {
            $dollarInvested = $value * $part->getPercent() / 100;
            $coin = $part->getCoin();
            $nbCoinBought = round(($dollarInvested / $coin->getPriceUsd()), 8);

            $position = new Position($user);
            $position->setOpenedAt($today)
                ->setCoin($coin)
                ->setEntryCost($dollarInvested)
                ->setNbCoins($nbCoinBought)
                ->setIsOpened(true)
                ->setIsDca(true)
                ->setDescription("DCA " . $part->getPercent() . "%");
            $positions[] = $position;
        }

        return $positions;
    }
}