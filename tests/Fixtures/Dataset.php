<?php

namespace App\Tests\Fixtures;

use App\Deposit\Entity\Deposit;
use App\Deposit\Entity\DepositType;
use App\Entity\Blockchain;
use App\Entity\CoinPercentDca;
use App\Entity\Cryptocurrency;
use App\Entity\Dapp;
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
use Doctrine\ORM\EntityManagerInterface;

/**
 * Small deterministic dataset covering every entity, persisted in the test transaction (rolled back by DAMA).
 * Coins get a libelle so the prePersist listener never calls CoinGecko.
 */
final class Dataset
{
    public const string PASSWORD = 'password';

    public FiatCurrency $usd;
    public FiatCurrency $eur;
    public Cryptocurrency $btc;
    public Cryptocurrency $eth;
    public Cryptocurrency $usdc;
    public Blockchain $ethereum;
    public Dapp $aave;
    public Exchange $exchange;
    public DepositType $depositType;
    public TypeProject $typeProject;
    public User $user;
    public User $admin;
    public Position $position;
    public Deposit $deposit;
    public Loan $loan;
    public Nft $nft;
    public StrategyFarming $farming;
    public StrategyLp $lp;
    public StrategyDca $dca;
    public ProjectMonitoring $project;

    public static function load(EntityManagerInterface $em): self
    {
        $d = new self();

        // USD and EUR are seeded by a migration.
        $currencies = $em->getRepository(FiatCurrency::class);
        $d->usd = $currencies->findOneBy(['fixerKey' => 'USD']) ?? throw new \LogicException('USD currency missing, run the migrations.');
        $d->eur = $currencies->findOneBy(['fixerKey' => 'EUR']) ?? throw new \LogicException('EUR currency missing, run the migrations.');

        $d->btc = self::coin('bitcoin', 'Bitcoin', 'btc', 50000, 1_000_000_000);
        $d->eth = self::coin('ethereum', 'Ethereum', 'eth', 2000, 500_000_000);
        $d->usdc = self::coin('usd-coin', 'USD Coin', 'usdc', 1, 100_000_000)->setIsStable(true);

        $d->ethereum = new Blockchain()->setLibelle('Ethereum')->setCoin($d->eth);
        $d->aave = new Dapp()->setLibelle('AAVE')->setUrl('https://app.aave.com/')->setBlockchain($d->ethereum);
        $d->exchange = new Exchange()->setLibelle('Binance')->setUrl('https://www.binance.com/');
        $d->depositType = new DepositType()->setLibelle('Transfer');
        $d->typeProject = new TypeProject()->setLibelle('Gaming');

        $d->user = self::user('user@example.com', [], $d->eur);
        $d->admin = self::user('admin@example.com', ['ROLE_ADMIN'], $d->usd);

        $d->position = new Position($d->user)->setCoin($d->btc)->setNbCoins(0.5)->setEntryCost(20000)
            ->setOpenedAt(new \DateTimeImmutable('2024-01-01'));
        $stablePosition = new Position($d->user)->setCoin($d->usdc)->setNbCoins(1000)->setEntryCost(1000);

        $d->deposit = new Deposit($d->user)->setAmount(1000)->setExchange($d->exchange)->setType($d->depositType)
            ->setFiatCurrency($d->eur)->setDepositedAt(new \DateTimeImmutable('2024-01-01'));
        $d->loan = new Loan($d->user)->setCoin($d->usdc)->setNbCoins(500)->setDapp($d->aave)->setDescription('');
        $d->nft = new Nft($d->user)->setCollection('Punks')->setBlockchain($d->ethereum)->setCryptocurrency($d->eth)
            ->setPriceCrypto(1)->setPriceUsd(2000);
        $d->farming = new StrategyFarming($d->user)->setCoin($d->eth)->setNbCoins(2)->setApr(5)->setDapp($d->aave)->setDescription('');
        $d->lp = new StrategyLp($d->user)->setCoin1($d->eth)->setCoin2($d->usdc)->setDapp($d->aave)
            ->setNbCoin1(1)->setNbCoin2(2000)->setPriceCoin1(2000)->setPriceCoin2(1)->setApr(10)->setDescription('');
        $d->dca = new StrategyDca($d->user)->setFiatToDcaEur(100)->setFarmingToDcaUsd(0)
            ->addPart(new CoinPercentDca()->setCoin($d->btc)->setPercent(60))
            ->addPart(new CoinPercentDca()->setCoin($d->eth)->setPercent(40));
        $d->project = new ProjectMonitoring($d->user)->setLibelle('Project')->setType($d->typeProject)->setCoin($d->eth)
            ->setNote('Note')->setDescription('')->setLinks(['https://example.com']);

        foreach ([$d->btc, $d->eth, $d->usdc, $d->ethereum, $d->aave, $d->exchange, $d->depositType,
            $d->typeProject, $d->user, $d->admin, $d->position, $stablePosition, $d->deposit, $d->loan, $d->nft,
            $d->farming, $d->lp, $d->dca, $d->project] as $entity) {
            $em->persist($entity);
        }
        $em->flush();

        return $d;
    }

    private static function coin(string $coingeckoId, string $libelle, string $symbol, float $priceUsd, float $mcapUsd): Cryptocurrency
    {
        return new Cryptocurrency()->setLibelleCoingecko($coingeckoId)->setLibelle($libelle)->setSymbol($symbol)
            ->setPriceUsd($priceUsd)->setMcapUsd($mcapUsd)->setIsStable(false);
    }

    /**
     * @param list<string> $roles
     */
    private static function user(string $email, array $roles, FiatCurrency $currency): User
    {
        // Pre-hashed "password" (bcrypt, cost 4) to skip the hasher.
        return new User()->setEmail($email)->setRoles($roles)->setFavoriteFiatCurrency($currency)
            ->setPassword(password_hash(self::PASSWORD, \PASSWORD_BCRYPT, ['cost' => 4]));
    }
}
