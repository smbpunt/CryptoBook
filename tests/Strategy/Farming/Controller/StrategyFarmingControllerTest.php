<?php

namespace App\Tests\Strategy\Farming\Controller;

use App\Strategy\Farming\Entity\StrategyFarming;
use App\Strategy\Lp\Entity\StrategyLp;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class StrategyFarmingControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
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
        // Total, then the crypto, stable and LP tabs.
        self::assertSame(['700 $', '200 $', '100 $', '400.00 $'], $this->yearlyStats($crawler));
    }

    public function testCreateFarming(): void
    {
        $crawler = $this->client->request('GET', '/strategy/farming/new');
        $values = $crawler->selectButton('Créer')->form()->getPhpValues();
        $values['strategy_farming'] = [
            'coin' => (string) $this->data->eth->getId(),
            'nbCoins' => '3',
            'apr' => '4',
            'blockchain' => (string) $this->data->ethereum->getId(),
            'dapp' => (string) $this->data->aave->getId(),
        ] + $values['strategy_farming'];

        $this->client->request('POST', '/strategy/farming/new', $values);

        self::assertResponseRedirects('/strategy/farming/');
        self::assertSame(1, $this->em()->getRepository(StrategyFarming::class)->count(['owner' => $this->data->user, 'nbCoins' => 3]));
    }

    public function testCreateLp(): void
    {
        $crawler = $this->client->request('GET', '/strategy/lp/new');
        $values = $crawler->filter('form[name="strategy_lp"]')->form()->getPhpValues();
        $values['strategy_lp'] = [
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

        self::assertResponseRedirects('/strategy/farming/');
        self::assertSame(1, $this->em()->getRepository(StrategyLp::class)->count(['owner' => $this->data->user, 'nbCoin1' => 2]));
    }

    /**
     * @return list<string> the "Année" stat of each yield block
     */
    private function yearlyStats(Crawler $crawler): array
    {
        return $crawler->filterXPath("//*[@data-slot='card-description'][normalize-space()='Année']/following-sibling::*[1]")
            ->each(static fn (Crawler $value): string => trim($value->text()));
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
