<?php

namespace App\Tests\Cryptocurrency\Form;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CryptocurrencyAutocompleteFieldTest extends WebTestCase
{
    private const string URL = '/autocomplete/cryptocurrency_autocomplete_field';

    public function testFormPointsToTheEndpoint(): void
    {
        $client = self::createClient();
        $d = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($d->user);

        $crawler = $client->request('GET', "/position/{$d->position->getId()}/edit");

        $select = $crawler->filter('#position_coin');
        self::assertStringStartsWith(self::URL, (string) $select->attr('data-symfony--ux-autocomplete--autocomplete-url-value'));
        // Only the selected coin is rendered, the others come from the endpoint.
        self::assertSame(['', (string) $d->btc->getId()], $select->filter('option')->each(static fn ($option): string => (string) $option->attr('value')));
    }

    public function testSearchesBySymbolOrNameBiggestMarketCapFirst(): void
    {
        $client = self::createClient();
        $d = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($d->user);

        $client->request('GET', self::URL);
        self::assertResponseIsSuccessful();
        self::assertSame(['BTC', 'ETH', 'USDC'], $this->labels($client->getResponse()->getContent()));

        $client->request('GET', self::URL.'?query=usd');
        self::assertSame(['USDC'], $this->labels($client->getResponse()->getContent()));

        $client->request('GET', self::URL.'?query=ether');
        self::assertSame([['value' => (string) $d->eth->getId(), 'text' => 'ETH']], json_decode((string) $client->getResponse()->getContent(), true)['results']);
    }

    public function testAnonymousIsRejected(): void
    {
        $client = self::createClient();

        $client->request('GET', self::URL);

        self::assertResponseRedirects('/login');
    }

    /**
     * @return list<string>
     */
    private function labels(string|false $json): array
    {
        return array_column(json_decode((string) $json, true)['results'], 'text');
    }
}
