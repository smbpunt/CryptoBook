<?php

namespace App\DataFixtures;

use App\Entity\Blockchain;
use App\Entity\CoinPercentDca;
use App\Entity\Cryptocurrency;
use App\Entity\Dapp;
use App\Entity\Deposit;
use App\Entity\DepositType;
use App\Entity\Exchange;
use App\Entity\FiatCurrency;
use App\Entity\Loan;
use App\Entity\Nft;
use App\Entity\Position;
use App\Entity\ProjectMonitoring;
use App\Entity\StrategyDca;
use App\Entity\StrategyFarming;
use App\Entity\StrategyLp;
use App\Entity\TypeProject;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Coins are filled here so that the prePersist listener does not call CoinGecko

        // The purger empties the currencies seeded by the migrations: recreate them
        $currencies = [];
        foreach ([
            ['Euro', 'EUR', '€', ['USD' => 1.073941, 'AUD' => 1.556436]],
            ['Dollar', 'USD', '$', ['EUR' => 0.930795, 'AUD' => 1.450125]],
            ['Dollar Australien', 'AUD', '$A', ['EUR' => 0.641872, 'USD' => 0.689596]],
        ] as [$libelle, $key, $symbol, $rates]) {
            $currency = (new FiatCurrency())->setLibelle($libelle)->setFixerKey($key)->setSymbol($symbol)
                ->setRates($rates)->setUpdatedAt(new \DateTimeImmutable());
            $manager->persist($currency);
            $currencies[] = $currency;
        }

        $btc = new Cryptocurrency();
        $btc->setLibelleCoingecko('bitcoin')->setLibelle('Bitcoin')->setSymbol('BTC')->setPriceUsd(60000);
        self::setLogos($btc, '1/%s/bitcoin.png?1696501400');
        $manager->persist($btc);

        $eth = new Cryptocurrency();
        $eth->setLibelleCoingecko('ethereum')->setLibelle('Ethereum')->setSymbol('ETH')->setPriceUsd(3000);
        self::setLogos($eth, '279/%s/ethereum.png?1696501628');
        $manager->persist($eth);

        $a = new Cryptocurrency();
        $a->setLibelleCoingecko('binancecoin')->setLibelle('BNB')->setSymbol('BNB')->setPriceUsd(550);
        self::setLogos($a, '825/%s/bnb-icon2_2x.png?1696501970');
        $manager->persist($a);

        $b = new Cryptocurrency();
        $b->setLibelleCoingecko('aave')->setLibelle('Aave')->setSymbol('AAVE')->setPriceUsd(100);
        self::setLogos($b, '12645/%s/aave-token-round.png?1720472354');
        $manager->persist($b);

        $c = new Cryptocurrency();
        $c->setLibelleCoingecko('avalanche-2')->setLibelle('Avalanche')->setSymbol('AVAX')->setPriceUsd(30);
        self::setLogos($c, '12559/%s/Avalanche_Circle_RedWhite_Trans.png?1696512369');
        $manager->persist($c);

        $d = new Cryptocurrency();
        $d->setLibelleCoingecko('ripple')->setLibelle('XRP')->setSymbol('XRP')->setPriceUsd(0.5);
        self::setLogos($d, '44/%s/xrp-symbol-white-128.png?1696501442');
        $manager->persist($d);

        $e = new Cryptocurrency();
        $e->setLibelleCoingecko('solana')->setLibelle('Solana')->setSymbol('SOL')->setPriceUsd(150);
        self::setLogos($e, '4128/%s/solana.png?1718769756');
        $manager->persist($e);

        $f = new Cryptocurrency();
        $f->setLibelleCoingecko('polkadot')->setLibelle('Polkadot')->setSymbol('DOT')->setPriceUsd(7);
        self::setLogos($f, '12171/%s/polkadot.jpg?1766533446');
        $manager->persist($f);

        $g = new Cryptocurrency();
        $g->setLibelleCoingecko('cardano')->setLibelle('Cardano')->setSymbol('ADA')->setPriceUsd(0.45);
        self::setLogos($g, '975/%s/cardano.png?1696502090');
        $manager->persist($g);

        $stable = new Cryptocurrency();
        $stable->setLibelleCoingecko('usd-coin')->setLibelle('USDC')->setSymbol('USDC')->setPriceUsd(1)->setIsStable(true);
        self::setLogos($stable, '6319/%s/USDC.png?1769615602');
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
        $binance->setLibelle('Binance')->setUrl('https://www.binance.com/fr');
        $manager->persist($binance);
        $ftx = new Exchange();
        $ftx->setLibelle('FTX')->setUrl('https://ftx.com/');
        $manager->persist($ftx);

        $gaming = new TypeProject();
        $gaming->setLibelle('Gaming');
        $manager->persist($gaming);
        $nft = new TypeProject();
        $nft->setLibelle('NFT');
        $manager->persist($nft);

        for ($u = 0; $u < 10; ++$u) {
            $user = new User();
            $user->setEmail($faker->email)
                ->setFavoriteFiatCurrency($faker->randomElement($currencies))
                ->setPassword($this->hasher->hashPassword($user, 'password'));

            for ($p = 0; $p < random_int(2, 4); ++$p) {
                $position = new Position($user);
                $nbCoins = $faker->randomFloat(4, 0.01, 0.1);
                $position->setCoin($btc)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($faker->randomFloat(2, 500, 3000))
                    ->setOpenedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(2, 6); ++$p) {
                $position = new Position($user);
                $nbCoins = $faker->randomFloat(2, 0.1, 1);
                $position->setCoin($eth)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($faker->randomFloat(2, 100, 1000))
                    ->setOpenedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(2, 6); ++$p) {
                $position = new Position($user);
                $nbCoins = $faker->randomFloat(2, 0.1, 1);
                $position->setCoin($a)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($faker->randomFloat(2, 100, 1000))
                    ->setOpenedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(2, 6); ++$p) {
                $position = new Position($user);
                $nbCoins = $faker->randomFloat(2, 0.1, 1);
                $position->setCoin($b)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($faker->randomFloat(2, 100, 1000))
                    ->setOpenedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(2, 6); ++$p) {
                $position = new Position($user);
                $nbCoins = $faker->randomFloat(2, 0.1, 1);
                $position->setCoin($c)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($faker->randomFloat(2, 100, 1000))
                    ->setOpenedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(2, 6); ++$p) {
                $position = new Position($user);
                $nbCoins = $faker->randomFloat(2, 0.1, 1);
                $position->setCoin($d)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($faker->randomFloat(2, 100, 1000))
                    ->setOpenedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(2, 6); ++$p) {
                $position = new Position($user);
                $nbCoins = $faker->randomFloat(2, 0.1, 1);
                $position->setCoin($e)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($faker->randomFloat(2, 100, 1000))
                    ->setOpenedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(2, 6); ++$p) {
                $position = new Position($user);
                $nbCoins = $faker->randomFloat(2, 0.1, 1);
                $position->setCoin($f)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($faker->randomFloat(2, 100, 1000))
                    ->setOpenedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(2, 6); ++$p) {
                $position = new Position($user);
                $nbCoins = $faker->randomFloat(2, 0.1, 1);
                $position->setCoin($g)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($faker->randomFloat(2, 100, 1000))
                    ->setOpenedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(1, 2); ++$p) {
                $position = new Position($user);
                $nbCoins = $faker->randomFloat(0, 1000, 5000);
                $position->setCoin($stable)
                    ->setNbCoins($nbCoins)
                    ->setRemainingCoins($nbCoins)
                    ->setEntryCost($nbCoins)
                    ->setOpenedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }

            for ($p = 0; $p < random_int(1, 3); ++$p) {
                $farming = new StrategyFarming($user);
                $farming->setEnteredAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                    ->setApr($faker->randomFloat(2, 0, 20))
                    ->setNbCoins($faker->randomFloat(4, 0.001, 0.1))
                    ->setCoin($btc)
                    ->setDapp($aave)
                    ->setDescription($faker->paragraph());
                $manager->persist($farming);
            }

            for ($p = 0; $p < random_int(1, 3); ++$p) {
                // farming lp
                $farming = new StrategyLp($user);
                $farming->setDapp($aave)
                    ->setCoin1($btc)
                    ->setCoin2($eth)
                    ->setNbCoin1($faker->randomFloat(4, 0.001, 0.1))
                    ->setNbCoin2($faker->randomFloat(4, 0.01, 1))
                    ->setPriceCoin1($faker->randomFloat(2, 20000, 30000))
                    ->setPriceCoin2($faker->randomFloat(2, 2000, 3000))
                    ->setStartAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                    ->setApr($faker->randomFloat(2, 1, 5))
                    ->setDescription($faker->paragraph());
                $manager->persist($farming);
            }

            for ($p = 0; $p < random_int(1, 3); ++$p) {
                // emprunts
                $loan = new Loan($user);
                $loan->setNbCoins($faker->randomFloat(2, 1000, 10000))
                    ->setCoin($stable)
                    ->setDapp($curve)
                    ->setLoanedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
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

            for ($p = 0; $p < random_int(1, 3); ++$p) {
                // depots
                $deposit1 = new Deposit($user);
                $deposit1->setDepositedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                    ->setExchange($binance)
                    ->setAmount($faker->randomFloat(0, 50, 200))
                    ->setType($cb)
                    ->setFiatCurrency($user->getFavoriteFiatCurrency());
                $manager->persist($deposit1);
                $deposit2 = new Deposit($user);
                $deposit2->setDepositedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                    ->setExchange($ftx)
                    ->setAmount($faker->randomFloat(0, 50, 200))
                    ->setType($vir)
                    ->setFiatCurrency($user->getFavoriteFiatCurrency());
                $manager->persist($deposit2);
            }

            for ($p = 0; $p < random_int(1, 3); ++$p) {
                // nft
                $nft = new Nft($user);
                $nft->setBlockchain($eth_chain)
                    ->setCryptocurrency($eth)
                    ->setCollection($faker->name())
                    ->setSupply($faker->randomNumber(5))
                    ->setNum($faker->randomNumber(4))
                    ->setRank($faker->randomNumber(4))
                    ->setPurchasedOn(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                    ->setPriceCrypto($faker->randomFloat(3, 0.1, 1))
                    ->setPriceUsd($faker->randomFloat(2, 300, 3000));
                $manager->persist($nft);
            }

            for ($p = 0; $p < random_int(1, 3); ++$p) {
                // projets
                $proj = new ProjectMonitoring($user);
                $proj->setLibelle($faker->name())
                    ->setType($gaming)
                    ->setCoin($eth)
                    ->setDescription($faker->paragraph())
                    ->setNote($faker->paragraph());

                for ($l = 0; $l < random_int(1, 3); ++$l) {
                    $link = "https://link.fr/$l";
                    $proj->addLink($link);
                }

                $manager->persist($proj);
            }

            $manager->persist($user);
        }
        $manager->flush();
    }

    /**
     * CoinGecko logo URLs, "%s" being the size (thumb, small, large).
     */
    private static function setLogos(Cryptocurrency $coin, string $path): void
    {
        $url = 'https://coin-images.coingecko.com/coins/images/'.$path;
        $coin->setUrlImgThumb(\sprintf($url, 'thumb'))
            ->setUrlImgSmall(\sprintf($url, 'small'))
            ->setUrlImgLarge(\sprintf($url, 'large'));
    }
}
