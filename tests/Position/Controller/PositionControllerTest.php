<?php

namespace App\Tests\Position\Controller;

use App\Position\Entity\Position;
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
