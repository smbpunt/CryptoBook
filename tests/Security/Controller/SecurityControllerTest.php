<?php

namespace App\Tests\Security\Controller;

use App\Tests\Fixtures\Dataset;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityControllerTest extends WebTestCase
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

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
