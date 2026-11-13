<?php

namespace App\Tests\Loan\Controller;

use App\Blockchain\Entity\Blockchain;
use App\Blockchain\Entity\Dapp;
use App\Loan\Entity\Loan;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

final class LoanControllerTest extends WebTestCase
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

    public function testXhrPostRendersTheDappsOfTheBlockchainWithoutSaving(): void
    {
        $polygon = new Blockchain()->setLibelle('Polygon')->setCoin($this->data->eth);
        $quickswap = new Dapp()->setLibelle('QuickSwap')->setUrl('https://quickswap.exchange/')->setBlockchain($polygon);
        $this->em()->persist($polygon);
        $this->em()->persist($quickswap);
        $this->em()->flush();

        $crawler = $this->client->request('GET', '/loan/new');
        $form = $crawler->selectButton('Ajouter')->form();
        $values = $form->getPhpValues();
        $values['loan']['blockchain'] = (string) $this->data->ethereum->getId();
        $values['loan']['nbCoins'] = '100';
        $values['loan']['coin'] = (string) $this->data->usdc->getId();

        $crawler = $this->client->xmlHttpRequest('POST', '/loan/new', $values);

        // The dapp is not chosen yet: the reload skips the validation, no error under the field.
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('#id-dapp [data-slot="form-message"]'));
        self::assertCount(1, $crawler->filter('#loan_dapp option[value="'.$this->data->aave->getId().'"]'));
        self::assertCount(0, $crawler->filter('#loan_dapp option[value="'.$quickswap->getId().'"]'));
        self::assertSame(1, $this->countLoans());
    }

    public function testCreateLoan(): void
    {
        $crawler = $this->client->request('GET', '/loan/new');
        $values = $crawler->selectButton('Ajouter')->form()->getPhpValues();
        $values['loan']['blockchain'] = (string) $this->data->ethereum->getId();
        $values['loan']['dapp'] = (string) $this->data->aave->getId();
        $values['loan']['nbCoins'] = '100';
        $values['loan']['coin'] = (string) $this->data->usdc->getId();

        $this->client->request('POST', '/loan/new', $values);

        self::assertResponseRedirects('/loan/');
        self::assertSame(2, $this->countLoans());
    }

    public function testEmptyQuantityIsAFormError(): void
    {
        $crawler = $this->client->request('GET', '/loan/new');
        $values = $crawler->selectButton('Ajouter')->form()->getPhpValues();
        $values['loan']['blockchain'] = (string) $this->data->ethereum->getId();
        $values['loan']['dapp'] = (string) $this->data->aave->getId();
        $values['loan']['nbCoins'] = '';
        $values['loan']['coin'] = (string) $this->data->usdc->getId();

        $this->client->request('POST', '/loan/new', $values);

        self::assertResponseStatusCodeSame(422);
        self::assertSame(1, $this->countLoans());
    }

    /**
     * @return iterable<string, array{array<string, string>, string}>
     */
    public static function invalidLoans(): iterable
    {
        yield 'no coins' => [['nbCoins' => '0'], 'strictement positive'];
        yield 'in the future' => [['loanedAt' => '2026-03-12'], 'La date ne peut pas être dans le futur.'];
    }

    /**
     * @param array<string, string> $fields
     */
    #[DataProvider('invalidLoans')]
    public function testInvalidLoanIsAFormError(array $fields, string $message): void
    {
        $crawler = $this->client->request('GET', '/loan/new');
        $values = $crawler->selectButton('Ajouter')->form()->getPhpValues();
        $values['loan'] = $fields + [
            'blockchain' => (string) $this->data->ethereum->getId(),
            'dapp' => (string) $this->data->aave->getId(),
            'nbCoins' => '100',
            'coin' => (string) $this->data->usdc->getId(),
        ] + $values['loan'];

        $this->client->request('POST', '/loan/new', $values);

        self::assertResponseStatusCodeSame(422);
        self::assertAnySelectorTextContains('[data-slot="form-message"]', $message);
        self::assertSame(1, $this->countLoans());
    }

    public function testRejectedAutocompleteSelectIsMarkedInvalid(): void
    {
        $crawler = $this->client->request('GET', '/loan/new');
        $values = $crawler->selectButton('Ajouter')->form()->getPhpValues();
        $values['loan']['blockchain'] = (string) $this->data->ethereum->getId();
        $values['loan']['dapp'] = '';
        $values['loan']['nbCoins'] = '100';
        $values['loan']['coin'] = (string) $this->data->usdc->getId();

        $this->client->request('POST', '/loan/new', $values);

        // The Tom Select control takes the error style from the hidden <select> (app.css).
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('#loan_dapp[aria-invalid="true"][data-controller~="symfony--ux-autocomplete--autocomplete"]');
        self::assertSelectorNotExists('#loan_coin[aria-invalid]');
    }

    public function testIndexShowsTheTotal(): void
    {
        $this->client->request('GET', '/loan/');

        self::assertSelectorTextContains('main', "Emprunt total 500\u{00A0}$");
    }

    private function countLoans(): int
    {
        return $this->em()->getRepository(Loan::class)->count(['owner' => $this->data->user]);
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
