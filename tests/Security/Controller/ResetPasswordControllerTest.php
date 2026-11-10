<?php

namespace App\Tests\Security\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ResetPasswordControllerTest extends WebTestCase
{
    public function testInvalidResetTokenSendsBackToTheRequestFormWithAnError(): void
    {
        $client = self::createClient();

        $client->request('GET', '/reset-password/reset/not-a-valid-token');
        self::assertResponseRedirects('/reset-password/reset');

        $client->followRedirect();
        self::assertResponseRedirects('/reset-password');
        self::assertSessionHasFlashMessage(
            'reset_password_error',
            'There was a problem validating your password reset request - The reset password link is invalid. Please try to reset your password again.',
        );
    }
}
