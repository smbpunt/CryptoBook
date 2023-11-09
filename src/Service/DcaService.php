<?php

namespace App\Service;

use App\Entity\Position;
use App\Entity\StrategyDca;
use App\Form\DcaAutoType;
use App\Repository\CryptocurrencyRepository;
use DateTime;
use DateTimeImmutable;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;

class DcaService
{
    /**
     * @param CryptocurrencyRepository $cryptocurrencyRepository
     */
    public function __construct(
        private readonly CryptocurrencyRepository $cryptocurrencyRepository
    )
    {
    }

    /**
     * @param StrategyDca $dca
     * @param UserInterface $user
     * @param float $value
     * @return Position[]
     */
    public function generatePositions(StrategyDca $dca, UserInterface $user, float $value): array
    {
        $positions = [];
        $today = DateTimeImmutable::createFromFormat('Y-m-d H:i', date('Y-m-d H:i'));

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

    /**
     * @return Position[]
     */
    public function generateDcaAutoPositions(UserInterface $user, string $idCrypto, string $recurr, string $nbRecurr, string $total, string $stringFirstDate): array
    {
        $coin = $this->cryptocurrencyRepository->find((int)$idCrypto);
        if ($coin === null) {
            return [];
        }

        try {
            $firstDate = new DateTime($stringFirstDate);
        } catch (Exception $e) {
            return [];
        }


        $modify = match ($recurr) {
            DcaAutoType::$HOURLY => '+4 hours',
            DcaAutoType::$DAILY => '+1 day',
            DcaAutoType::$WEEKLY => '+1 week',
            DcaAutoType::$MONTHLY => '+1 month',
            default => null
        };

        if ($modify === null) {
            return [];
        }

        $positions = [];
        for ($i = 0; $i < (int)$nbRecurr; $i++) {
            if ($i > 0) {
                $firstDate->modify($modify);
            }

            $position = new Position($user);
            $position->setOpenedAt(DateTimeImmutable::createFromMutable($firstDate))
                ->setCoin($coin)
                ->setEntryCost((float)$total / (float)$nbRecurr)
                ->setIsOpened(true)
                ->setIsDca(true)
                ->setDescription("DCA Auto");

            $positions[] = $position;
        }

        return $positions;
    }
}