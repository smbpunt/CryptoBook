<?php

namespace App\DataFixtures;

use App\Entity\Blockchain;
use App\Entity\CoinPercentDca;
use App\Entity\Cryptocurrency;
use App\Entity\Dapp;
use App\Entity\Deposit;
use App\Entity\DepositType;
use App\Entity\Exchange;
use App\Entity\Loan;
use App\Entity\Nft;
use App\Entity\Position;
use App\Entity\ProjectMonitoring;
use App\Entity\StrategyDca;
use App\Entity\StrategyFarming;
use App\Entity\StrategyLp;
use App\Entity\TypeProject;
use App\Entity\User;
use App\Service\CryptocurrencyService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private CryptocurrencyService $cryptocurrencyService;
    private UserPasswordHasherInterface $hasher;

    public function __construct(CryptocurrencyService $cryptocurrencyService, UserPasswordHasherInterface $hasher)
    {
        $this->cryptocurrencyService = $cryptocurrencyService;
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');

        $btc = new Cryptocurrency();
        $btc->setLibelleCoingecko('bitcoin');
        $manager->persist($btc);

        $eth = new Cryptocurrency();
        $eth->setLibelleCoingecko('ethereum');
        $manager->persist($eth);

        $stable = new Cryptocurrency();
        $stable->setLibelleCoingecko('usd-coin')->setIsStable(true);
        $manager->persist($stable);


        $btc_chain = new Blockchain();
        $btc_chain->setCoin($btc)->setLibelle('bitcoin');
        $manager->persist($btc_chain);
        $eth_chain = new Blockchain();
        $eth_chain->setCoin($eth)->setLibelle('ethereum');
        $manager->persist($eth_chain);

        $aave = new Dapp();
        $aave->setLibelle('AAVE')->setUrl('https://app.aave.com/')->setBlockchain($eth_chain);
        $manager->persist($aave);
        $curve = new Dapp();
        $curve->setLibelle('Curve')->setUrl('https://polygon.curve.fi/')->setBlockchain($eth_chain);
        $manager->persist($curve);

        $cb = new DepositType();
        $cb->setLibelle('Carte Bleu');
        $manager->persist($cb);
        $vir = new DepositType();
        $vir->setLibelle('Virement');
        $manager->persist($vir);

        $binance = new Exchange();
        $binance->setLibelle('Binance')->setUrl("https://www.binance.com/fr");
        $manager->persist($binance);
        $ftx = new Exchange();
        $ftx->setLibelle('FTX')->setUrl("https://ftx.com/");
        $manager->persist($ftx);

        $gaming = new TypeProject();
        $gaming->setLibelle("Gaming");
        $manager->persist($gaming);
        $nft = new TypeProject();
        $nft->setLibelle("NFT");
        $manager->persist($nft);

        for ($u = 0; $u < 10; $u++) {
            $user = new User();
            $user->setEmail($faker->email)
                ->setPassword($this->hasher->hashPassword($user, 'password'));

            for ($p = 0; $p < random_int(2, 4); $p++) {
                $position = new Position($user);
                $nbCoins =$faker->randomFloat(4, 0.01, 0.1);
                $position->setCoin($btc)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($faker->randomFloat(2, 500, 3000))
                    ->setOpenedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }


            for ($p = 0; $p < random_int(2, 6); $p++) {
                $position = new Position($user);
                $nbCoins =$faker->randomFloat(2, 0.1, 1);
                $position->setCoin($eth)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($faker->randomFloat(2, 100, 1000))
                    ->setOpenedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(1, 2); $p++) {
                $position = new Position($user);
                $nbCoins =$faker->randomFloat(0, 1000, 5000);
                $position->setCoin($stable)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($nbCoins)
                    ->setOpenedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(1, 3); $p++) {
                $farming = new StrategyFarming($user);
                $farming->setEnteredAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                    ->setApr($faker->randomFloat(2, 0, 20))
                    ->setNbCoins($faker->randomFloat(4, 0.001, 0.1))
                    ->setCoin($btc)
                    ->setDapp($aave)
                    ->setDescription($faker->paragraph());
                $manager->persist($farming);
            }

            for ($p = 0; $p < random_int(1, 3); $p++) {
                //farming lp
                $farming = new StrategyLp($user);
                $farming->setDapp($aave)
                    ->setCoin1($btc)
                    ->setCoin2($eth)
                    ->setNbCoin1($faker->randomFloat(4, 0.001, 0.1))
                    ->setNbCoin2($faker->randomFloat(4, 0.01, 1))
                    ->setPriceCoin1($faker->randomFloat(2, 20000, 30000))
                    ->setPriceCoin2($faker->randomFloat(2, 2000, 3000))
                    ->setStartAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                    ->setApr($faker->randomFloat(2, 1, 5))
                    ->setDescription($faker->paragraph());
                $manager->persist($farming);
            }

            for ($p = 0; $p < random_int(1, 3); $p++) {
                //emprunts
                $loan = new Loan($user);
                $loan->setNbCoins($faker->randomFloat(2, 1000, 10000))
                    ->setCoin($stable)
                    ->setDapp($curve)
                    ->setLoanedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                    ->setDescription($faker->paragraph());
                $manager->persist($loan);
            }

            $dca = new StrategyDca($user);
            $dca->setFarmingToDcaUsd($faker->randomFloat(1, 10, 100))
                ->setFiatToDcaEur($faker->randomFloat(0, 50, 200));
            $dca_part_eth = new CoinPercentDca();
            $dca_part_eth->setPercent(50)->setCoin($eth);
            $dca->addPart($dca_part_eth);
            $dca_part_btc = new CoinPercentDca();
            $dca_part_btc->setPercent(50)->setCoin($btc);
            $dca->addPart($dca_part_btc);
            $manager->persist($dca);


            for ($p = 0; $p < random_int(1, 3); $p++) {
                //depots
                $deposit1 = new Deposit($user);
                $deposit1->setDepositedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                    ->setExchange($binance)
                    ->setValueEur($faker->randomFloat(0, 50, 200))
                    ->setType($cb);
                $manager->persist($deposit1);
                $deposit2 = new Deposit($user);
                $deposit2->setDepositedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                    ->setExchange($ftx)
                    ->setValueEur($faker->randomFloat(0, 50, 200))
                    ->setType($vir);
                $manager->persist($deposit2);
            }

            for ($p = 0; $p < random_int(1, 3); $p++) {
                //nft
                $nft = new Nft($user);
                $nft->setBlockchain($eth_chain)
                    ->setCryptocurrency($eth)
                    ->setCollection($faker->name())
                    ->setSupply($faker->randomNumber(5))
                    ->setNum($faker->randomNumber(4))
                    ->setRank($faker->randomNumber(4))
                    ->setPurchasedOn(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                    ->setPriceCrypto($faker->randomFloat(3, 0.1, 1))
                    ->setPriceUsd($faker->randomFloat(2, 300, 3000));
                $manager->persist($nft);
            }

            for ($p = 0; $p < random_int(1, 3); $p++) {
                //projets
                $proj = new ProjectMonitoring($user);
                $proj->setLibelle($faker->name())
                    ->setType($gaming)
                    ->setCoin($eth)
                    ->setDescription($faker->paragraph())
                    ->setNote($faker->paragraph());

                for ($l = 0; $l < random_int(1, 3); $l++) {
                    $link = "https://link.fr/$l";
                    $proj->addLink($link);
                }

                $manager->persist($proj);
            }

            $manager->persist($user);
        }
        $manager->flush();
        //$this->cryptocurrencyService->updateAllCryptos();
    }
}
