<?php

namespace App\Tests\Strategy\Farming\Controller;

use App\Strategy\Farming\Entity\StrategyFarming;
use App\Strategy\Lp\Entity\StrategyLp;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\DomCrawler\Crawler;

final class StrategyFarmingControllerTest extends WebTestCase
{
    use ClockSensitiveTrait;

    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        self::mockTime('2026-03-10 12:00 UTC');
        $this->client = self::createClient();
        $this->data = Dataset::load($this->em());
        $this->client->loginUser($this->data->user);
    }

    public function testShowsTheYearlyRewards(): void
    {
        // 2 ETH at 5 % = 200 $, 1 000 USDC at 10 % = 100 $, LP of 4 000 $ at 10 % = 400 $.
        $this->em()->persist(new StrategyFarming($this->data->user)->setCoin($this->data->usdc)->setNbCoins(1000)->setApr(10)
            ->setDapp($this->data->aave)->setDescription(''));
        $this->em()->flush();

        $crawler = $this->client->request('GET', '/strategy/farming/');

        self::assertResponseIsSuccessful();
        // Total, then crypto, stable and LP.
        self::assertSame(["1,92\u{00A0}$ / jour · 13,46\u{00A0}$ / sem. · 700,00\u{00A0}$ / an", "200,00\u{00A0}$ / an", "100,00\u{00A0}$ / an", "400,00\u{00A0}$ / an"], $this->yearlyStats($crawler));
    }

    public function testLpLiveValueAndRewardsUseTheCurrentPrices(): void
    {
        // LP entered with 1 ETH at 2 000 $ + 2 000 USDC, ETH now at 3 000 $: 5 000 $ at 10 % = 500 $ a year.
        $this->data->eth->setPriceUsd(3000);
        $this->em()->flush();

        $crawler = $this->client->request('GET', '/strategy/farming/');

        $row = $crawler->filter('[data-slot="tabs-content"]')->last()->filter('tbody tr')->first()->filter('td')
            ->each(static fn (Crawler $cell): string => trim($cell->text()));
        // Invested, value, APR, then the rewards of a day, a month and a year.
        self::assertSame(["4\u{202F}000,00\u{00A0}$", "5\u{202F}000,00\u{00A0}$", "10,0\u{00A0}%", "1,37\u{00A0}$", "41,67\u{00A0}$", "500,00\u{00A0}$"], \array_slice($row, 2, 6));
        self::assertSame("500,00\u{00A0}$ / an", $this->yearlyStats($crawler)[3]);
    }

    public function testInfosOfAFarmingWithoutDapp(): void
    {
        // Saved before the dapp was required.
        $farming = new StrategyFarming($this->data->user)->setCoin($this->data->eth)->setNbCoins(1)->setApr(5)->setDescription('');
        $this->em()->persist($farming);
        $this->em()->flush();

        $this->client->request('POST', "/strategy/farming/{$farming->getId()}/infos");

        self::assertResponseIsSuccessful();
    }

    public function testCreateFarming(): void
    {
        $this->postFarming(['nbCoins' => '3']);

        self::assertResponseRedirects('/strategy/farming/');
        self::assertSame(1, $this->em()->getRepository(StrategyFarming::class)->count(['owner' => $this->data->user, 'nbCoins' => 3]));
    }

    public function testFarmingWithoutQuantityIsAFormError(): void
    {
        $this->postFarming(['nbCoins' => '']);

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreateLp(): void
    {
        $this->postLp([]);

        self::assertResponseRedirects('/strategy/farming/');
        self::assertSame(1, $this->em()->getRepository(StrategyLp::class)->count(['owner' => $this->data->user, 'nbCoin1' => 2]));
    }

    public function testLpWithoutAprIsAFormError(): void
    {
        $this->postLp(['apr' => '']);

        self::assertResponseStatusCodeSame(422);
    }

    /**
     * @return iterable<string, array{array<string, string>, string}>
     */
    public static function invalidFarmings(): iterable
    {
        yield 'no coins' => [['nbCoins' => '0'], 'strictement positive'];
        yield 'negative APR' => [['apr' => '-1'], 'supérieure ou égale à zéro'];
        yield 'in the future' => [['enteredAt' => '2026-03-12'], 'La date ne peut pas être dans le futur.'];
        yield 'no dapp' => [['dapp' => ''], 'Ce champ est obligatoire.'];
    }

    /**
     * @param array<string, string> $fields
     */
    #[DataProvider('invalidFarmings')]
    public function testInvalidFarmingIsAFormError(array $fields, string $message): void
    {
        $this->postFarming($fields);

        self::assertResponseStatusCodeSame(422);
        self::assertAnySelectorTextContains('[data-slot="form-message"]', $message);
        self::assertSame(1, $this->em()->getRepository(StrategyFarming::class)->count(['owner' => $this->data->user]));
    }

    /**
     * @return iterable<string, array{array<string, string>, string}>
     */
    public static function invalidLps(): iterable
    {
        yield 'zero price' => [['priceCoin1' => '0'], 'strictement positive'];
        yield 'negative quantity' => [['nbCoin1' => '-1'], 'supérieure ou égale à zéro'];
        yield 'no quantity' => [['nbCoin1' => '0', 'nbCoin2' => '0'], 'Au moins une des deux quantités doit être supérieure à zéro.'];
        yield 'zero deposit' => [['lpDeposit' => '0'], 'strictement positive'];
        yield 'negative APR' => [['apr' => '-1'], 'supérieure ou égale à zéro'];
        yield 'in the future' => [['startAt' => '2026-03-12'], 'La date ne peut pas être dans le futur.'];
    }

    /**
     * @param array<string, string> $fields
     */
    #[DataProvider('invalidLps')]
    public function testInvalidLpIsAFormError(array $fields, string $message): void
    {
        $this->postLp($fields);

        self::assertResponseStatusCodeSame(422);
        self::assertAnySelectorTextContains('[data-slot="form-message"]', $message);
        self::assertSame(1, $this->em()->getRepository(StrategyLp::class)->count(['owner' => $this->data->user]));
    }

    public function testLpOutOfItsRangeHoldsASingleCoin(): void
    {
        $this->postLp(['nbCoin1' => '0']);

        self::assertResponseRedirects('/strategy/farming/');
    }

    public function testLpWithTheSameCoinTwiceIsAFormError(): void
    {
        $this->postLp(['coin2' => (string) $this->data->eth->getId()]);

        self::assertResponseStatusCodeSame(422);
        self::assertAnySelectorTextContains('[data-slot="form-message"]', 'Les deux cryptos doivent être différentes.');
    }

    #[DataProvider('dependentForms')]
    public function testXhrPostRendersTheDappsOfTheBlockchainWithoutSaving(string $url, string $name, ?string $entity): void
    {
        if (null !== $entity) {
            $url = \sprintf($url, $this->data->{$entity}->getId());
        }
        $crawler = $this->client->request('GET', $url);
        $values = $crawler->filter('form[name="'.$name.'"]')->form()->getPhpValues();
        $values[$name]['blockchain'] = (string) $this->data->ethereum->getId();
        $values[$name]['dapp'] = '';

        $crawler = $this->client->xmlHttpRequest('POST', $url, $values);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('#id-dapp option[value="'.$this->data->aave->getId().'"]'));
        self::assertCount(0, $crawler->filter('#id-dapp [data-slot="form-message"]'));
        // Nothing created, nothing changed.
        $this->em()->clear();
        self::assertSame(1, $this->em()->getRepository(StrategyFarming::class)->count(['owner' => $this->data->user]));
        self::assertSame(1, $this->em()->getRepository(StrategyLp::class)->count(['owner' => $this->data->user]));
        self::assertSame(1, $this->em()->getRepository(StrategyFarming::class)->count(['owner' => $this->data->user, 'dapp' => $this->data->aave]));
        self::assertSame(1, $this->em()->getRepository(StrategyLp::class)->count(['owner' => $this->data->user, 'dapp' => $this->data->aave]));
    }

    /**
     * @return iterable<string, array{string, string, ?string}> URL, form name, Dataset entity of an edit URL
     */
    public static function dependentForms(): iterable
    {
        yield 'new farming' => ['/strategy/farming/new', 'strategy_farming', null];
        yield 'farming edit' => ['/strategy/farming/%d/edit', 'strategy_farming', 'farming'];
        yield 'new lp' => ['/strategy/lp/new', 'strategy_lp', null];
        yield 'lp edit' => ['/strategy/lp/%d/edit', 'strategy_lp', 'lp'];
    }

    /**
     * @param array<string, string> $fields
     */
    private function postFarming(array $fields): void
    {
        $crawler = $this->client->request('GET', '/strategy/farming/new');
        $values = $crawler->selectButton('Ajouter')->form()->getPhpValues();
        $values['strategy_farming'] = $fields + [
            'coin' => (string) $this->data->eth->getId(),
            'nbCoins' => '3',
            'apr' => '4',
            'blockchain' => (string) $this->data->ethereum->getId(),
            'dapp' => (string) $this->data->aave->getId(),
        ] + $values['strategy_farming'];

        $this->client->request('POST', '/strategy/farming/new', $values);
    }

    /**
     * @param array<string, string> $fields
     */
    private function postLp(array $fields): void
    {
        $crawler = $this->client->request('GET', '/strategy/lp/new');
        $values = $crawler->filter('form[name="strategy_lp"]')->form()->getPhpValues();
        $values['strategy_lp'] = $fields + [
            'coin1' => (string) $this->data->eth->getId(),
            'coin2' => (string) $this->data->usdc->getId(),
            'priceCoin1' => '2000',
            'priceCoin2' => '1',
            'nbCoin1' => '2',
            'nbCoin2' => '4000',
            'apr' => '12',
            'blockchain' => (string) $this->data->ethereum->getId(),
            'dapp' => (string) $this->data->aave->getId(),
        ] + $values['strategy_lp'];

        $this->client->request('POST', '/strategy/lp/new', $values);
    }

    /**
     * @return list<string> the hint of each revenue card: total, crypto, stable, LP
     */
    private function yearlyStats(Crawler $crawler): array
    {
        return $crawler->filter('main [data-slot="card-header"] > p')
            ->each(static fn (Crawler $value): string => trim($value->text()));
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
