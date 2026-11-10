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

    public function testLoginFromAnotherOriginIsRejected(): void
    {
        $this->client->request('GET', '/login');
        $this->client->request('POST', '/login', [
            'email' => 'user@example.com',
            'password' => Dataset::PASSWORD,
            '_csrf_token' => 'csrf-token',
        ], server: ['HTTP_ORIGIN' => 'https://evil.example', 'HTTP_REFERER' => 'https://evil.example/']);

        self::assertResponseRedirects('/login');
        $this->em()->clear();
        self::assertNull($this->em()->getRepository(User::class)->find($this->data->user->getId())->getLastConnectedAt());
    }

    public function testTabOrderReachesThePasswordBeforeTheForgottenPasswordLink(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $focusable = $crawler->filter('form input:not([type=hidden]), form button, form a')->each(
            static fn ($node): string => $node->attr('name') ?? $node->attr('href') ?? $node->text(),
        );
        self::assertSame(['email', 'password', '/reset-password', '_remember_me', 'Se connecter', '/register'], $focusable);
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
