<?php

namespace App\Tests\Position\Controller;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Position\Entity\Position;
use App\Position\Entity\StrategyPosition;
use App\Position\Entity\Vente;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @return iterable<string, array{array<string, string>, list<array<string, string>>}>
     */
    public static function invalidPositions(): iterable
    {
        yield 'no coins' => [['nbCoins' => '0'], []];
        yield 'empty quantity' => [['nbCoins' => ''], []];
        yield 'negative cost' => [['entryCost' => '-1'], []];
        yield 'sale over 100 %' => [[], [['soldAt' => '2024-02-01', 'percent' => '150', 'priceSold' => '3000']]];
        yield 'negative sale price' => [[], [['soldAt' => '2024-02-01', 'percent' => '50', 'priceSold' => '-1']]];
    }

    /**
     * @param array<string, string>       $fields
     * @param list<array<string, string>> $sales
     */
    #[DataProvider('invalidPositions')]
    public function testInvalidPositionIsAFormError(array $fields, array $sales): void
    {
        $crawler = $this->client->request('GET', '/position/new');
        $values = $crawler->selectButton('Valider')->form()->getPhpValues();
        $values['position'] = $fields + ['coin' => (string) $this->data->eth->getId(), 'nbCoins' => '1', 'entryCost' => '1000'] + $values['position'];
        $values['position']['ventes'] = $sales;

        $this->client->request('POST', '/position/new', $values);

        self::assertResponseStatusCodeSame(422);
    }

    public function testExitPlanOverOneHundredPercentIsAFormError(): void
    {
        $crawler = $this->client->request('GET', "/position/{$this->data->position->getId()}/edit");
        $values = $crawler->selectButton('Valider')->form()->getPhpValues();
        $values['position']['strategies'] = [['percent' => '120', 'priceSold' => '100000']];

        $this->client->request('POST', "/position/{$this->data->position->getId()}/edit", $values);

        self::assertResponseStatusCodeSame(422);
    }

    public function testInfosOfAFreePositionWithAnExitPlan(): void
    {
        $free = new Position($this->data->user)->setCoin($this->data->eth)->setNbCoins(1)->setEntryCost(0)
            ->addStrategy(new StrategyPosition()->setPercent(50)->setPriceSold(4000));
        $this->persist($free);

        $this->client->request('POST', "/position/{$free->getId()}/infos");

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Gains/pertes : 2000 $ (+ ∞)');
    }

    public function testFreePositionShowsAnInfiniteEvolution(): void
    {
        $this->persist(new Position($this->data->user)->setCoin($this->data->eth)->setNbCoins(1)->setEntryCost(0));

        $crawler = $this->client->request('GET', '/position/');

        self::assertSame('+ ∞', trim($crawler->filter('[id^="detail-"]')->last()->filter('tbody tr td')->eq(6)->text()));
    }

    public function testAverageCostOfACoinUnderOneCent(): void
    {
        $shib = new Cryptocurrency()->setLibelleCoingecko('shiba-inu')->setLibelle('Shiba Inu')->setSymbol('shib')
            ->setPriceUsd(0.00002)->setMcapUsd(1)->setIsStable(false);
        $this->em()->persist($shib);
        // 1 000 000 SHIB for 10 $: 0.00001 $ each, rounded to 0 by getEntryCoinValue().
        $this->persist(new Position($this->data->user)->setCoin($shib)->setNbCoins(1_000_000)->setEntryCost(10));

        $crawler = $this->client->request('GET', '/position/');

        $row = self::cells($crawler->filterXPath("//tr[td[contains(normalize-space(.), ' SHIB')]]")->first());
        self::assertSame('100 %', $row[5]);
    }

    public function testClosedStablePositionIsInTheClosedTab(): void
    {
        $this->persist(new Position($this->data->user)->setCoin($this->data->usdc)->setNbCoins(10)->setEntryCost(10)->setIsOpened(false));

        $crawler = $this->client->request('GET', '/position/');

        self::assertSame('Fermée (1)', trim($crawler->filter('[role="tab"]')->eq(2)->text()));
    }

    public function testSubmitAndNextOpensTheFormAgain(): void
    {
        $this->client->request('GET', '/position/new');
        $this->client->submitForm('Valider & continuer', [
            'position[coin]' => (string) $this->data->eth->getId(),
            'position[nbCoins]' => '1',
            'position[entryCost]' => '1000',
        ]);

        self::assertResponseRedirects('/position/new');
    }

    public function testSubmitAndNextFromTheEditFormOpensTheCreationForm(): void
    {
        $this->client->request('GET', "/position/{$this->data->position->getId()}/edit");
        $this->client->submitForm('Valider & continuer');

        self::assertResponseRedirects('/position/new');
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

    public function testPositionFormTabsDoNotSubmit(): void
    {
        $crawler = $this->client->request('GET', '/position/new');

        $tabs = $crawler->filter('form[name="position"] [role="tab"]');
        self::assertCount(2, $tabs);
        self::assertSame(['button', 'button'], $tabs->each(static fn ($tab): ?string => $tab->attr('type')), 'A tab inside the form must not submit it.');
        // Enter submits through the first submit button of the form.
        self::assertSame('position[submit]', $crawler->filter('form[name="position"] button[type="submit"]')->first()->attr('name'));
    }

    public function testCreatePositionWithSale(): void
    {
        $crawler = $this->client->request('GET', '/position/new');

        // Sales are a JS collection: post the raw values with one sale added.
        $form = $crawler->selectButton('Valider')->form([
            'position[coin]' => (string) $this->data->eth->getId(),
            'position[nbCoins]' => '4',
            'position[entryCost]' => '1000',
            'position[openedAt]' => '2024-01-01',
        ]);
        $values = $form->getPhpValues();
        $values['position']['ventes'][0] = ['soldAt' => '2024-02-01', 'percent' => '25', 'priceSold' => '3000'];
        $this->client->request('POST', '/position/new', $values);

        self::assertResponseRedirects('/position/');
        $position = $this->em()->getRepository(Position::class)->findOneBy(['owner' => $this->data->user, 'coin' => $this->data->eth]);
        self::assertNotNull($position);
        self::assertSame(1000.0, $position->getEntryCost());
        self::assertSame(3.0, $position->getRemainingCoins());
        self::assertCount(1, $position->getVentes());
        self::assertSame('2024-01-01', $position->getOpenedAt()?->format('Y-m-d'));
        self::assertSame('2024-02-01', $position->getVentes()->first()->getSoldAt()?->format('Y-m-d'));
        self::assertTrue($position->isIsOpened());
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
