<?php

namespace App\Tests\Deposit\Controller;

use App\Deposit\Entity\Deposit;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DepositControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->data = Dataset::load($this->em());
        $this->client->loginUser($this->data->user);
    }

    public function testIndexShowsTheTotalInUsdAndInTheFavoriteCurrency(): void
    {
        $this->client->request('GET', '/deposit/');

        self::assertResponseIsSuccessful();
        // Migration rates: 1 EUR = 1.073941 USD.
        self::assertSelectorTextContains('main', "1\u{202F}000\u{00A0}€");
        self::assertSelectorTextContains('main', "soit 1\u{202F}074\u{00A0}$");
    }

    public function testEditDeposit(): void
    {
        $this->client->request('GET', "/deposit/{$this->data->deposit->getId()}/edit");
        $this->client->submitForm('Enregistrer', ['deposit[amount]' => '1500']);

        self::assertResponseRedirects('/deposit/');
        $this->em()->clear();
        self::assertSame(1500.0, $this->em()->find(Deposit::class, $this->data->deposit->getId())?->getAmount());
    }

    public function testEmptyAmountIsAFormError(): void
    {
        $this->client->request('GET', '/deposit/new');
        $this->client->submitForm('Ajouter', [
            'deposit[amount]' => '',
            'deposit[exchange]' => (string) $this->data->exchange->getId(),
            'deposit[fiatCurrency]' => (string) $this->data->usd->getId(),
            'deposit[type]' => (string) $this->data->depositType->getId(),
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreateDeposit(): void
    {
        $this->client->request('GET', '/deposit/new');
        $this->client->submitForm('Ajouter', [
            'deposit[amount]' => '250',
            'deposit[exchange]' => (string) $this->data->exchange->getId(),
            'deposit[fiatCurrency]' => (string) $this->data->usd->getId(),
            'deposit[type]' => (string) $this->data->depositType->getId(),
        ]);

        self::assertResponseRedirects('/deposit/');
        $deposits = $this->em()->getRepository(Deposit::class)->findBy(['owner' => $this->data->user, 'amount' => 250]);
        self::assertCount(1, $deposits);
        self::assertSame($this->data->usd->getId(), $deposits[0]->getFiatCurrency()->getId());
        self::assertSame($this->data->exchange->getId(), $deposits[0]->getExchange()->getId());
        self::assertSame($this->data->depositType->getId(), $deposits[0]->getType()->getId());
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
