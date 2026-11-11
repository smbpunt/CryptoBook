<?php

namespace App\Tests\Nft\Controller;

use App\Nft\Entity\Nft;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class NftControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->data = Dataset::load($this->em());
        $this->client->loginUser($this->data->user);
    }

    public function testCreateNft(): void
    {
        $this->submitNewForm($this->validValues());

        self::assertResponseRedirects('/nft/');
        self::assertSame(1, $this->em()->getRepository(Nft::class)->count(['owner' => $this->data->user, 'collection' => 'Apes']));
    }

    public function testEmptyPriceIsAFormError(): void
    {
        $this->submitNewForm(['nft[priceUsd]' => ''] + $this->validValues());

        self::assertResponseStatusCodeSame(422);
    }

    public function testIndexShowsTheProfitOfTheSoldNftsOnly(): void
    {
        // Bought 1 000 $, sold 1 500 $. The held NFT of the Dataset does not count.
        $this->em()->persist(new Nft($this->data->user)->setCollection('Apes')->setBlockchain($this->data->ethereum)
            ->setCryptocurrency($this->data->eth)->setPriceCrypto(0.5)->setPriceUsd(1000)
            ->setSoldOn(new \DateTimeImmutable('2024-07-01'))->setPriceSoldUsd(1500));
        $this->em()->flush();

        $this->client->request('GET', '/nft/');

        self::assertSelectorTextContains('main', 'Résultat : 500 $');
    }

    /**
     * @param array<string, string> $values
     */
    private function submitNewForm(array $values): void
    {
        $crawler = $this->client->request('GET', '/nft/new');
        // The coin select only holds the selected option: Tom Select adds the one picked from the endpoint.
        $this->client->submit($crawler->selectButton('Ajouter')->form()->disableValidation()->setValues($values));
    }

    /**
     * @return array<string, string>
     */
    private function validValues(): array
    {
        return [
            'nft[collection]' => 'Apes',
            'nft[priceCrypto]' => '1.5',
            'nft[priceUsd]' => '3000',
            'nft[blockchain]' => (string) $this->data->ethereum->getId(),
            'nft[cryptocurrency]' => (string) $this->data->eth->getId(),
        ];
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
