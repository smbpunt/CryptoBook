<?php

namespace App\Tests\Admin\Controller;

use App\FiatCurrency\Entity\FiatCurrency;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

/**
 * A currency always has its USD rate: it is fetched from Fixer when the currency is created.
 */
final class FiatCurrencyCrudControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        // Keeps the mocked Fixer answer from one request to the next.
        $this->client->disableReboot();
        $this->data = Dataset::load($this->em());
        $this->client->loginUser($this->data->admin);
    }

    public function testCreationFetchesTheRates(): void
    {
        $this->fixerAnswers(new JsonMockResponse(['rates' => ['USD' => 1.13, 'EUR' => 1.05, 'AUD' => 1.7]]));

        $this->create(['libelle' => 'Franc suisse', 'fixerKey' => 'CHF', 'symbol' => 'CHF']);

        self::assertResponseRedirects();
        $chf = $this->em()->getRepository(FiatCurrency::class)->findOneBy(['fixerKey' => 'CHF']);
        self::assertNotNull($chf);
        self::assertSame(1.13, $chf->rateTo('USD'));
        self::assertNotNull($chf->getUpdatedAt());
    }

    public function testCodeUnknownToFixerIsAFormError(): void
    {
        $this->fixerAnswers(new JsonMockResponse(['success' => false, 'error' => ['code' => 201]]));

        $this->create(['libelle' => 'Franc suisse', 'fixerKey' => 'CHF', 'symbol' => 'CHF']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('form[name="FiatCurrency"]', 'Fixer ne donne pas de taux');
        self::assertNull($this->em()->getRepository(FiatCurrency::class)->findOneBy(['fixerKey' => 'CHF']));
    }

    public function testCodeOutsideIso4217IsAFormError(): void
    {
        $this->fixerAnswers(new JsonMockResponse(['rates' => ['USD' => 1.0]]));

        $this->create(['libelle' => 'Inconnue', 'fixerKey' => 'ABC', 'symbol' => 'A']);

        self::assertResponseStatusCodeSame(422);
        self::assertNull($this->em()->getRepository(FiatCurrency::class)->findOneBy(['fixerKey' => 'ABC']));
    }

    public function testEmptyFieldsAreFormErrors(): void
    {
        $this->fixerAnswers(new JsonMockResponse(['rates' => ['USD' => 1.0]]));

        $this->create(['libelle' => '', 'fixerKey' => '', 'symbol' => '']);

        self::assertResponseStatusCodeSame(422);
    }

    public function testDuplicateCodeIsAFormError(): void
    {
        $this->fixerAnswers(new JsonMockResponse(['rates' => ['USD' => 1.07]]));

        $this->create(['libelle' => 'Euro', 'fixerKey' => 'EUR', 'symbol' => '€']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('form[name="FiatCurrency"]', 'Cette valeur est déjà utilisée.');
        self::assertSame(1, $this->em()->getRepository(FiatCurrency::class)->count(['fixerKey' => 'EUR']));
    }

    public function testCodeIsReadOnlyOnEdit(): void
    {
        $crawler = $this->client->request('GET', "/admin/fiat-currency/{$this->data->eur->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertNotNull($crawler->filter('input[name="FiatCurrency[fixerKey]"]')->attr('disabled'));
    }

    /**
     * @param array<string, string> $fields
     */
    private function create(array $fields): void
    {
        $crawler = $this->client->request('GET', '/admin/fiat-currency/new');
        $form = $crawler->filter('form[name="FiatCurrency"]')->form();
        foreach ($fields as $name => $value) {
            $form["FiatCurrency[$name]"] = $value;
        }
        $this->client->submit($form);
    }

    private function fixerAnswers(JsonMockResponse $response): void
    {
        $http = self::getContainer()->get('http_client.transport');
        \assert($http instanceof MockHttpClient);
        $http->setResponseFactory($response);
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
