<?php

namespace App\Tests\Security\Controller;

use App\Tests\Fixtures\Dataset;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationControllerTest extends WebTestCase
{
    private const string STRONG_PASSWORD = 'cheval-batterie-agrafe';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        Dataset::load($this->em());
    }

    public function testRegistrationLogsTheNewUserIn(): void
    {
        $this->register('new@example.com', self::STRONG_PASSWORD);

        self::assertResponseRedirects('/');
        $user = $this->em()->getRepository(User::class)->findOneBy(['email' => 'new@example.com']);
        self::assertNotNull($user);
        self::assertSame('USD', $user->getFavoriteFiatCurrency()->getFixerKey());
        self::assertTrue(self::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($user, self::STRONG_PASSWORD));

        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSame('new@example.com', self::getContainer()->get('security.untracked_token_storage')->getToken()?->getUserIdentifier());
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function invalidSubmissions(): iterable
    {
        yield 'weak password' => ['new@example.com', 'secret123', 'Mot de passe trop faible'];
        yield 'empty password' => ['new@example.com', '', 'Veuillez saisir un mot de passe.'];
        yield 'invalid email' => ['not-an-email', self::STRONG_PASSWORD, 'Cet email n\'est pas valide.'];
        yield 'empty email' => ['', self::STRONG_PASSWORD, 'Veuillez saisir un email.'];
        yield 'existing email' => ['user@example.com', self::STRONG_PASSWORD, 'Un compte existe déjà avec cet email.'];
    }

    #[DataProvider('invalidSubmissions')]
    public function testInvalidRegistrationIsRejected(string $email, string $password, string $error): void
    {
        $this->register($email, $password);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('[data-slot="form-message"]', $error);
        self::assertSame('user@example.com' === $email ? 1 : 0, $this->em()->getRepository(User::class)->count(['email' => $email]));
    }

    public function testFormFieldsFollowTheReadingOrder(): void
    {
        $crawler = $this->client->request('GET', '/register');

        $focusable = $crawler->filter('form input:not([type=hidden]), form button, form a')->each(
            static fn ($node): string => $node->attr('name') ?? $node->attr('href') ?? $node->text(),
        );
        self::assertSame(['registration_form[email]', 'registration_form[plainPassword]', 'S\'inscrire', '/login'], $focusable);
    }

    private function register(string $email, string $password): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('S\'inscrire', [
            'registration_form[email]' => $email,
            'registration_form[plainPassword]' => $password,
        ]);
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
