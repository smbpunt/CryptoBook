<?php

namespace App\Tests\Nft\Controller;

use App\Nft\Entity\Nft;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

final class NftControllerTest extends WebTestCase
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

    /**
     * @return iterable<string, array{array<string, string>, string}>
     */
    public static function invalidNfts(): iterable
    {
        yield 'empty collection' => [['nft[collection]' => ''], 'Ce champ est obligatoire.'];
        yield 'collection too long' => [['nft[collection]' => str_repeat('a', 256)], 'trop longue'];
        yield 'negative number' => [['nft[num]' => '-1'], 'supérieure ou égale à zéro'];
        yield 'number over the column' => [['nft[num]' => '2147483648'], 'inférieure ou égale'];
        yield 'zero rank' => [['nft[rank]' => '0'], 'strictement positive'];
        yield 'zero supply' => [['nft[supply]' => '0'], 'strictement positive'];
        yield 'negative price' => [['nft[priceUsd]' => '-1'], 'supérieure ou égale à zéro'];
        yield 'fees over 100 %' => [['nft[percentSaleFees]' => '101'], 'comprise entre 0 et 100'];
        yield 'bought in the future' => [['nft[purchasedOn]' => '2026-03-12'], 'La date ne peut pas être dans le futur.'];
        yield 'sold before the purchase' => [['nft[purchasedOn]' => '2024-02-01', 'nft[soldOn]' => '2024-01-01', 'nft[priceSoldUsd]' => '4000'], 'La vente ne peut pas précéder l\'achat.'];
        yield 'sold without price' => [['nft[soldOn]' => '2024-06-01'], 'Le prix de vente en dollars est obligatoire pour un NFT vendu.'];
        yield 'sale price without date' => [['nft[priceSoldUsd]' => '4000'], 'La date de vente est obligatoire quand un prix de vente est saisi.'];
        yield 'crypto sale price without date' => [['nft[priceSoldCrypto]' => '1.2'], 'La date de vente est obligatoire quand un prix de vente est saisi.'];
    }

    /**
     * @param array<string, string> $fields
     */
    #[DataProvider('invalidNfts')]
    public function testInvalidNftIsAFormError(array $fields, string $message): void
    {
        $this->submitNewForm($fields + $this->validValues());

        self::assertResponseStatusCodeSame(422);
        self::assertAnySelectorTextContains('[data-slot="form-message"]', $message);
        self::assertSame(1, $this->em()->getRepository(Nft::class)->count(['owner' => $this->data->user]));
    }

    public function testIndexShowsTheProfitOfTheSoldNftsOnly(): void
    {
        // Bought 1 000 $, sold 1 500 $. The held NFT of the Dataset does not count.
        $this->em()->persist(new Nft($this->data->user)->setCollection('Apes')->setBlockchain($this->data->ethereum)
            ->setCryptocurrency($this->data->eth)->setPriceCrypto(0.5)->setPriceUsd(1000)
            ->setSoldOn(new \DateTimeImmutable('2024-07-01'))->setPriceSoldUsd(1500));
        $this->em()->flush();

        $this->client->request('GET', '/nft/');

        self::assertSelectorTextContains('main', "Résultat des ventes +500\u{00A0}$");
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
            'nft[purchasedOn]' => '2024-01-01',
            'nft[blockchain]' => (string) $this->data->ethereum->getId(),
            'nft[cryptocurrency]' => (string) $this->data->eth->getId(),
        ];
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
