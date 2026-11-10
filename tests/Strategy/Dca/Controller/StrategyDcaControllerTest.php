<?php

namespace App\Tests\Strategy\Dca\Controller;

use App\Position\Entity\Position;
use App\Strategy\Dca\Entity\StrategyDca;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class StrategyDcaControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->data = Dataset::load($this->em());
        $this->client->loginUser($this->data->user);
    }

    public function testIndexShowsTheAmountToInvest(): void
    {
        $crawler = $this->client->request('GET', '/strategy/dca/');

        self::assertResponseIsSuccessful();
        // 100 € at 1.073941 $: 107.39 $, 60 % in BTC at 50 000 $.
        self::assertSelectorTextContains('main', '~ 107.39 $');
        self::assertSame(['BTC', '60 %', '64.44 $', '0.001289'], $crawler->filter('main tbody tr')->first()->filter('td')
            ->each(static fn (Crawler $cell): string => trim($cell->text())));
    }

    public function testGeneratedPositionsAreSaved(): void
    {
        $this->client->request('GET', '/strategy/dca/generatePositions/1000');
        $this->client->submitForm('Valider');

        self::assertResponseRedirects('/position/');
        $positions = $this->em()->getRepository(Position::class)->findBy(['owner' => $this->data->user, 'isDca' => true], ['entryCost' => 'DESC']);
        self::assertCount(2, $positions);
        self::assertSame(600.0, $positions[0]->getEntryCost());
        self::assertSame(0.012, $positions[0]->getNbCoins());
    }

    public function testDcaAutoPositionsAreSaved(): void
    {
        $this->client->request('GET', '/strategy/dca/dcaAuto?'.http_build_query([
            'crypto' => $this->data->btc->getId(), 't' => 'd', 'nb' => 2, 'total' => 200, 'd' => '2024-01-01T10:00',
        ]));
        $this->client->submitForm('Valider', [
            'dca_to_position[positions][0][nbCoins]' => '0.002',
            'dca_to_position[positions][1][nbCoins]' => '0.0021',
        ]);

        self::assertResponseRedirects('/position/');
        $positions = $this->em()->getRepository(Position::class)->findBy(['owner' => $this->data->user, 'isDca' => true], ['openedAt' => 'ASC']);
        self::assertCount(2, $positions);
        self::assertSame('2024-01-02 10:00', $positions[1]->getOpenedAt()?->format('Y-m-d H:i'));
        self::assertSame(100.0, $positions[1]->getEntryCost());
    }

    public function testCreateStrategy(): void
    {
        $this->client->loginUser($this->data->admin);
        $crawler = $this->client->request('GET', '/strategy/dca/new');
        $values = $crawler->selectButton('Save')->form()->getPhpValues();
        $values['strategy_dca']['fiatToDcaEur'] = '50';
        $values['strategy_dca']['farmingToDcaUsd'] = '10';
        $values['strategy_dca']['parts'] = [
            ['coin' => (string) $this->data->btc->getId(), 'percent' => '100'],
        ];

        $this->client->request('POST', '/strategy/dca/new', $values);

        self::assertResponseRedirects('/strategy/dca/');
        $dca = $this->em()->getRepository(StrategyDca::class)->findOneBy(['owner' => $this->data->admin]);
        self::assertNotNull($dca);
        self::assertCount(1, $dca->getParts());
    }

    public function testEditStrategy(): void
    {
        $this->client->request('GET', '/strategy/dca/edit');
        $this->client->submitForm('Modifier', ['strategy_dca[fiatToDcaEur]' => '250']);

        self::assertResponseRedirects('/strategy/dca/');
        $this->em()->clear();
        self::assertSame(250.0, $this->em()->find(StrategyDca::class, $this->data->dca->getId())?->getFiatToDcaEur());
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
