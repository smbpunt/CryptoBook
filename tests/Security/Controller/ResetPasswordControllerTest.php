<?php

namespace App\Tests\Security\Controller;

use App\Tests\Fixtures\Dataset;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

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

    public function testWeakNewPasswordIsRejected(): void
    {
        $client = $this->openResetForm();

        $this->submitNewPassword($client, 'secret123');

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('[data-slot="form-message"]', 'Mot de passe trop faible');
    }

    public function testStrongNewPasswordIsSaved(): void
    {
        $client = $this->openResetForm();

        $this->submitNewPassword($client, 'cheval-batterie-agrafe');

        self::assertResponseRedirects('/login');
        $user = $this->em()->getRepository(User::class)->findOneBy(['email' => 'user@example.com']);
        self::assertTrue(self::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($user, 'cheval-batterie-agrafe'));
    }

    private function openResetForm(): KernelBrowser
    {
        $client = self::createClient();
        $data = Dataset::load($this->em());
        $token = self::getContainer()->get(ResetPasswordHelperInterface::class)->generateResetToken($data->user)->getToken();

        $client->request('GET', '/reset-password/reset/'.$token);
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        return $client;
    }

    private function submitNewPassword(KernelBrowser $client, string $password): void
    {
        $client->submitForm('Réinitialiser le mot de passe', [
            'change_password_form[plainPassword][first]' => $password,
            'change_password_form[plainPassword][second]' => $password,
        ]);
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
