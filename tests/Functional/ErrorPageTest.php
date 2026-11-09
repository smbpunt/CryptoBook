<?php

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Error pages use the custom template (only visible with debug off).
 */
final class ErrorPageTest extends WebTestCase
{
    public function testUnknownUrlRendersCustomNotFoundPage(): void
    {
        $client = self::createClient(['debug' => false]);
        $client->request('GET', '/does-not-exist');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextContains('[data-slot="empty-title"]', 'Page introuvable');
        self::assertSelectorNotExists('nav[aria-label="Navigation principale"]');
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function statuses(): iterable
    {
        yield '403' => [403, 'Accès refusé'];
        yield '500' => [500, 'Erreur serveur'];
        yield '502 falls back to 500' => [502, 'Erreur serveur'];
        yield '410 falls back to status text' => [410, 'Gone'];
    }

    #[DataProvider('statuses')]
    public function testErrorTemplateRendersEachStatus(int $status, string $title): void
    {
        $twig = self::getContainer()->get('twig');
        $html = $twig->render('@Twig/Exception/error.html.twig', [
            'status_code' => $status,
            'status_text' => 'Gone',
            'exception' => null,
        ]);

        self::assertStringContainsString($title, $html);
        self::assertStringContainsString((string) $status, $html);
    }
}
