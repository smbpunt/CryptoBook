<?php

namespace App\Tests\Functional;

use App\Deposit\Entity\Deposit;
use App\Entity\Position;
use App\Tests\Fixtures\Dataset;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FormTest extends WebTestCase
{
    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->data = Dataset::load($this->em());
    }

    public function testLogin(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', ['email' => 'user@example.com', 'password' => Dataset::PASSWORD]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        $this->em()->clear();
        self::assertNotNull($this->em()->getRepository(User::class)->find($this->data->user->getId())->getLastConnectedAt());
    }

    public function testLoginWithWrongPassword(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', ['email' => 'user@example.com', 'password' => 'wrong']);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorExists('[data-slot="alert"]');
    }

    public function testRegistration(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Register', [
            'registration_form[email]' => 'new@example.com',
            'registration_form[plainPassword]' => 'secret123',
            'registration_form[agreeTerms]' => true,
        ]);

        self::assertResponseRedirects('/');
        $user = $this->em()->getRepository(User::class)->findOneBy(['email' => 'new@example.com']);
        self::assertNotNull($user);
        self::assertSame('USD', $user->getFavoriteFiatCurrency()->getFixerKey());
        self::assertNotSame('secret123', $user->getPassword());
    }

    public function testPositionFormTabsDoNotSubmit(): void
    {
        $this->client->loginUser($this->data->user);
        $crawler = $this->client->request('GET', '/position/new');

        $tabs = $crawler->filter('form[name="position"] [role="tab"]');
        self::assertCount(2, $tabs);
        self::assertSame(['button', 'button'], $tabs->each(static fn ($tab): ?string => $tab->attr('type')), 'A tab inside the form must not submit it.');
        // Enter submits through the first submit button of the form.
        self::assertSame('position[submit]', $crawler->filter('form[name="position"] button[type="submit"]')->first()->attr('name'));
    }

    public function testCreatePositionWithSale(): void
    {
        $this->client->loginUser($this->data->user);
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

    public function testCreateDeposit(): void
    {
        $this->client->loginUser($this->data->user);
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

    public function testUpdatePreferences(): void
    {
        $this->client->loginUser($this->data->user);
        $this->client->request('GET', '/user/preferences');
        $this->client->submitForm('Enregistrer', ['user_preferences[favoriteFiatCurrency]' => (string) $this->data->usd->getId()]);

        self::assertResponseRedirects('/user/preferences');
        $this->em()->clear();
        $user = $this->em()->getRepository(User::class)->find($this->data->user->getId());
        self::assertSame('USD', $user->getFavoriteFiatCurrency()->getFixerKey());
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
