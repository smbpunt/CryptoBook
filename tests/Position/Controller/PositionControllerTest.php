<?php

namespace App\Tests\Position\Controller;

use App\Position\Entity\Position;
use App\Position\Entity\StrategyPosition;
use App\Position\Entity\Vente;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class PositionControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->data = Dataset::load($this->em());
        $this->client->loginUser($this->data->user);
    }

    public function testCoinsAreGroupedWithTheirAverageCost(): void
    {
        // 0.5 BTC at 40 000 $ (Dataset) + 0.5 BTC at 60 000 $: 1 BTC at 50 000 $, the current price.
        $this->persist(new Position($this->data->user)->setCoin($this->data->btc)->setNbCoins(0.5)->setEntryCost(30000));

        $crawler = $this->client->request('GET', '/position/');

        self::assertResponseIsSuccessful();
        $row = self::cells($crawler->filter('[data-slot="tabs-content"]')->first()->filter('tbody tr')->first());
        self::assertSame(['(2)', '', '', '1 BTC', '~ 50000 $', '0 %', '~ 50000 $'], $row);
    }

    public function testStableAndClosedTabs(): void
    {
        $this->persist(new Position($this->data->user)->setCoin($this->data->eth)->setNbCoins(1)->setEntryCost(1000)->setIsOpened(false));

        $crawler = $this->client->request('GET', '/position/');

        self::assertSame(['Crypto (1)', 'Stable (1)', 'Fermée (1)'], $crawler->filter('[role="tab"]')->each(static fn (Crawler $tab): string => trim($tab->text())));
    }

    public function testInfosShowTheExitPlan(): void
    {
        // 0.5 BTC for 20 000 $: sell half at 100 000 $.
        $this->data->position->addStrategy(new StrategyPosition()->setPercent(50)->setPriceSold(100000));
        $this->persist($this->data->position);

        $this->client->request('POST', "/position/{$this->data->position->getId()}/infos");

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Récupéré : 25000 $');
        self::assertSelectorTextContains('body', 'Gains/pertes : 5000 $ (25 %)');
    }

    public function testInfosShowTheSales(): void
    {
        $this->data->position->addVente(new Vente()->setSoldAt(new \DateTimeImmutable('2024-03-01'))->setPercent(20)->setPriceSold(60000));
        $this->persist($this->data->position);

        $crawler = $this->client->request('POST', "/position/{$this->data->position->getId()}/infos");

        self::assertResponseIsSuccessful();
        self::assertSame(['1', '01/03/2024', '20 %', '60000 $'], $crawler->filter('tbody tr')->first()->filter('td')->each(static fn (Crawler $cell): string => trim($cell->text())));
    }

    public function testEditPosition(): void
    {
        $this->client->request('GET', "/position/{$this->data->position->getId()}/edit");
        $this->client->submitForm('Valider', ['position[nbCoins]' => '0.75']);

        self::assertResponseRedirects('/position/');
        $this->em()->clear();
        self::assertSame(0.75, $this->em()->find(Position::class, $this->data->position->getId())?->getNbCoins());
    }

    public function testDeletePosition(): void
    {
        $id = $this->data->position->getId();
        $crawler = $this->client->request('GET', '/position/');

        $this->client->submit($crawler->filter("form[action=\"/position/$id\"]")->form());

        self::assertResponseRedirects('/position/');
        $this->em()->clear();
        self::assertNull($this->em()->find(Position::class, $id));
    }

    /**
     * @return list<string>
     */
    private static function cells(Crawler $row): array
    {
        return $row->filter('td')->each(static fn (Crawler $cell): string => trim(preg_replace('/\s+/', ' ', $cell->text())));
    }

    private function persist(Position $position): void
    {
        $this->em()->persist($position);
        $this->em()->flush();
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
