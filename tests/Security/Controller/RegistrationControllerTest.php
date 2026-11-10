<?php

namespace App\Tests\Security\Controller;

use App\Tests\Fixtures\Dataset;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegistrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        Dataset::load($this->em());
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

    public function testTheNewUserIsLoggedIn(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Register', [
            'registration_form[email]' => 'new@example.com',
            'registration_form[plainPassword]' => 'secret123',
            'registration_form[agreeTerms]' => true,
        ]);
        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSame('new@example.com', self::getContainer()->get('security.untracked_token_storage')->getToken()?->getUserIdentifier());
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
