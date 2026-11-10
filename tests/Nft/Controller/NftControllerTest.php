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
        $this->client->request('GET', '/nft/new');
        $this->client->submitForm('Ajouter', $this->validValues());

        self::assertResponseRedirects('/nft/');
        self::assertSame(1, $this->em()->getRepository(Nft::class)->count(['owner' => $this->data->user, 'collection' => 'Apes']));
    }

    public function testEmptyPriceIsAFormError(): void
    {
        $this->client->request('GET', '/nft/new');
        $this->client->submitForm('Ajouter', ['nft[priceUsd]' => ''] + $this->validValues());

        self::assertResponseStatusCodeSame(422);
    }

    public function testIndexShowsTheProfitOfTheSoldNfts(): void
    {
        // Bought 2 000 $, sold 3 000 $ with 10 % fees: 700 $. Bought 1 000 $, sold 500 $: -500 $.
        $this->data->nft->setSoldOn(new \DateTimeImmutable('2024-06-01'))->setPriceSoldUsd(3000)->setPercentSaleFees(10);
        $sold = new Nft($this->data->user)->setCollection('Apes')->setBlockchain($this->data->ethereum)
            ->setCryptocurrency($this->data->eth)->setPriceCrypto(0.5)->setPriceUsd(1000)
            ->setSoldOn(new \DateTimeImmutable('2024-07-01'))->setPriceSoldUsd(500);
        $held = new Nft($this->data->user)->setCollection('Held')->setBlockchain($this->data->ethereum)
            ->setCryptocurrency($this->data->eth)->setPriceCrypto(1)->setPriceUsd(2000);
        $this->em()->persist($sold);
        $this->em()->persist($held);
        $this->em()->flush();

        $this->client->request('GET', '/nft/');

        self::assertSelectorTextContains('main', 'Résultat : 200 $');
        self::assertSelectorTextContains('main', 'Vendu (2)');
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
