<?php

namespace App\Tests\Deposit\Controller;

use App\Deposit\Entity\Deposit;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

final class DepositControllerTest extends WebTestCase
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
        $this->submitNewForm([]);

        self::assertResponseRedirects('/deposit/');
        $deposits = $this->em()->getRepository(Deposit::class)->findBy(['owner' => $this->data->user, 'amount' => 250]);
        self::assertCount(1, $deposits);
        self::assertSame($this->data->usd->getId(), $deposits[0]->getFiatCurrency()->getId());
        self::assertSame($this->data->exchange->getId(), $deposits[0]->getExchange()->getId());
        self::assertSame($this->data->depositType->getId(), $deposits[0]->getType()->getId());
    }

    /**
     * @return iterable<string, array{array<string, string>, string}>
     */
    public static function invalidDeposits(): iterable
    {
        yield 'zero amount' => [['deposit[amount]' => '0'], 'strictement positive'];
        yield 'negative amount' => [['deposit[amount]' => '-50'], 'strictement positive'];
        yield 'in the future' => [['deposit[depositedAt]' => '2026-03-12'], 'La date ne peut pas être dans le futur.'];
    }

    /**
     * @param array<string, string> $fields
     */
    #[DataProvider('invalidDeposits')]
    public function testInvalidDepositIsAFormError(array $fields, string $message): void
    {
        $this->submitNewForm($fields);

        self::assertResponseStatusCodeSame(422);
        self::assertAnySelectorTextContains('[data-slot="form-message"]', $message);
        self::assertSame(1, $this->countDeposits());
    }

    public function testDepositDatedTomorrowIsAccepted(): void
    {
        // PHP runs in UTC: tomorrow is accepted so that the French "today" is never refused.
        $this->submitNewForm(['deposit[depositedAt]' => '2026-03-11']);

        self::assertResponseRedirects('/deposit/');
        self::assertSame(2, $this->countDeposits());
    }

    /**
     * @param array<string, string> $fields
     */
    private function submitNewForm(array $fields): void
    {
        $this->client->request('GET', '/deposit/new');
        $this->client->submitForm('Ajouter', $fields + [
            'deposit[amount]' => '250',
            'deposit[exchange]' => (string) $this->data->exchange->getId(),
            'deposit[fiatCurrency]' => (string) $this->data->usd->getId(),
            'deposit[type]' => (string) $this->data->depositType->getId(),
        ]);
    }

    private function countDeposits(): int
    {
        return $this->em()->getRepository(Deposit::class)->count(['owner' => $this->data->user]);
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
