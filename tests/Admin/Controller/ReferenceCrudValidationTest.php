<?php

namespace App\Tests\Admin\Controller;

use App\Blockchain\Entity\Blockchain;
use App\Blockchain\Entity\Dapp;
use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Deposit\Entity\DepositType;
use App\Exchange\Entity\Exchange;
use App\ProjectMonitoring\Entity\TypeProject;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The reference data of the admin: required fields, URLs and unique names.
 */
final class ReferenceCrudValidationTest extends WebTestCase
{
    private const array CLASSES = [
        'Blockchain' => Blockchain::class,
        'Dapp' => Dapp::class,
        'Exchange' => Exchange::class,
        'TypeProject' => TypeProject::class,
        'DepositType' => DepositType::class,
        'Cryptocurrency' => Cryptocurrency::class,
    ];

    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->data = Dataset::load($this->em());
        $this->client->loginUser($this->data->admin);
    }

    /**
     * Fields set to "eth" or "ethereum" take the id of that coin or blockchain of the Dataset.
     *
     * @return iterable<string, array{string, string, array<string, string>, string}>
     */
    public static function invalidCreations(): iterable
    {
        yield 'blockchain without name' => ['blockchain', 'Blockchain', ['libelle' => '', 'coin' => 'eth'], 'Ce champ est obligatoire.'];
        yield 'blockchain without coin' => ['blockchain', 'Blockchain', ['libelle' => 'Polygon', 'coin' => ''], 'Ce champ est obligatoire.'];
        yield 'blockchain twice' => ['blockchain', 'Blockchain', ['libelle' => 'Ethereum', 'coin' => 'eth'], 'Cette valeur est déjà utilisée.'];
        yield 'dapp with a script URL' => ['dapp', 'Dapp', ['libelle' => 'Uniswap', 'url' => 'javascript:alert(1)', 'blockchain' => 'ethereum'], 'Cette valeur n\'est pas une URL valide.'];
        yield 'dapp twice on a blockchain' => ['dapp', 'Dapp', ['libelle' => 'AAVE', 'url' => 'https://app.aave.com/', 'blockchain' => 'ethereum'], 'Cette valeur est déjà utilisée.'];
        yield 'dapp without blockchain' => ['dapp', 'Dapp', ['libelle' => 'Uniswap', 'url' => 'https://app.uniswap.org/', 'blockchain' => ''], 'Ce champ est obligatoire.'];
        yield 'exchange without URL' => ['exchange', 'Exchange', ['libelle' => 'Kraken', 'url' => ''], 'Ce champ est obligatoire.'];
        yield 'exchange twice' => ['exchange', 'Exchange', ['libelle' => 'Binance', 'url' => 'https://www.binance.com/'], 'Cette valeur est déjà utilisée.'];
        yield 'project type without name' => ['type-project', 'TypeProject', ['libelle' => ''], 'Ce champ est obligatoire.'];
        yield 'project type twice' => ['type-project', 'TypeProject', ['libelle' => 'Gaming'], 'Cette valeur est déjà utilisée.'];
        yield 'deposit type without name' => ['deposit-type', 'DepositType', ['libelle' => ''], 'Ce champ est obligatoire.'];
        yield 'deposit type twice' => ['deposit-type', 'DepositType', ['libelle' => 'Transfer'], 'Cette valeur est déjà utilisée.'];
        yield 'coin without CoinGecko id' => ['cryptocurrency', 'Cryptocurrency', ['libelleCoingecko' => ''], 'Ce champ est obligatoire.'];
    }

    /**
     * @param array<string, string> $fields
     */
    #[DataProvider('invalidCreations')]
    public function testInvalidCreationIsAFormError(string $slug, string $name, array $fields, string $message): void
    {
        $repository = $this->em()->getRepository(self::CLASSES[$name]);
        $count = $repository->count();

        $this->post("/admin/$slug/new", $name, $fields);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains("form[name=\"$name\"]", $message);
        self::assertSame($count, $repository->count());
    }

    public function testCreateExchange(): void
    {
        $this->post('/admin/exchange/new', 'Exchange', ['libelle' => 'Kraken', 'url' => 'https://www.kraken.com/']);

        self::assertResponseRedirects();
        self::assertSame(1, $this->em()->getRepository(Exchange::class)->count(['libelle' => 'Kraken']));
    }

    public function testDappWithTheSameNameOnAnotherBlockchain(): void
    {
        $polygon = new Blockchain()->setLibelle('Polygon')->setCoin($this->data->eth);
        $this->em()->persist($polygon);
        $this->em()->flush();

        $this->post('/admin/dapp/new', 'Dapp', ['libelle' => 'AAVE', 'url' => 'https://app.aave.com/', 'blockchain' => (string) $polygon->getId()]);

        self::assertResponseRedirects();
        self::assertSame(2, $this->em()->getRepository(Dapp::class)->count(['libelle' => 'AAVE']));
    }

    public function testInvalidCoinColor(): void
    {
        // A valid CSS color, but Chart.js only reads hex and the column holds 10 characters.
        $this->post("/admin/cryptocurrency/{$this->data->btc->getId()}/edit", 'Cryptocurrency', ['color' => 'red']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('form[name="Cryptocurrency"]', 'Cette valeur n\'est pas une couleur CSS valide.');
    }

    /**
     * Posts the raw values: the browser would refuse some of them (required fields, color input).
     *
     * @param array<string, string> $fields
     */
    private function post(string $url, string $name, array $fields): void
    {
        $crawler = $this->client->request('GET', $url);
        $form = $crawler->filter("form[name=\"$name\"]")->form();
        $values = $form->getPhpValues();
        foreach ($fields as $field => $value) {
            $value = match ($value) {
                'eth' => (string) $this->data->eth->getId(),
                'ethereum' => (string) $this->data->ethereum->getId(),
                default => $value,
            };
            // An autocomplete association posts its value in a child field.
            if ($form->has("{$name}[$field][autocomplete]")) {
                $values[$name][$field]['autocomplete'] = $value;
            } else {
                $values[$name][$field] = $value;
            }
        }

        $this->client->request('POST', $form->getUri(), $values);
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
