<?php

namespace App\Tests\Security\Controller;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegistrationControllerTest extends WebTestCase
{
    public function testTheNewUserIsLoggedIn(): void
    {
        $client = self::createClient();
        Dataset::load(self::getContainer()->get(EntityManagerInterface::class));

        $client->request('GET', '/register');
        $client->submitForm('Register', [
            'registration_form[email]' => 'new@example.com',
            'registration_form[plainPassword]' => 'secret123',
            'registration_form[agreeTerms]' => true,
        ]);
        self::assertResponseRedirects('/');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSame('new@example.com', self::getContainer()->get('security.untracked_token_storage')->getToken()?->getUserIdentifier());
    }
}
