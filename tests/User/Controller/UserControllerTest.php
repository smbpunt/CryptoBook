<?php

namespace App\Tests\User\Controller;

use App\Tests\Fixtures\Dataset;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->data = Dataset::load($this->em());
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

    public function testPreferencesWithoutCurrencyIsAFormError(): void
    {
        $this->client->loginUser($this->data->user);
        $crawler = $this->client->request('GET', '/user/preferences');
        $values = $crawler->selectButton('Enregistrer')->form()->getPhpValues();
        $values['user_preferences']['favoriteFiatCurrency'] = '';

        $this->client->request('POST', '/user/preferences', $values);

        self::assertResponseStatusCodeSame(422);
        self::assertAnySelectorTextContains('[data-slot="form-message"]', 'Ce champ est obligatoire.');
        $this->em()->clear();
        self::assertSame('EUR', $this->em()->find(User::class, $this->data->user->getId())?->getFavoriteFiatCurrency()?->getFixerKey());
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
