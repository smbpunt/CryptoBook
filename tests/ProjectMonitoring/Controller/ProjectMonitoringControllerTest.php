<?php

namespace App\Tests\ProjectMonitoring\Controller;

use App\ProjectMonitoring\Entity\ProjectMonitoring;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectMonitoringControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->data = Dataset::load($this->em());
        $this->client->loginUser($this->data->user);
    }

    public function testCreateProject(): void
    {
        $this->client->request('GET', '/project/monitoring/new');
        $this->client->submitForm('Ajouter', [
            'project_monitoring[libelle]' => 'New project',
            'project_monitoring[type]' => (string) $this->data->typeProject->getId(),
        ]);

        self::assertResponseRedirects('/project/monitoring/');
        $project = $this->em()->getRepository(ProjectMonitoring::class)->findOneBy(['owner' => $this->data->user, 'libelle' => 'New project']);
        self::assertNotNull($project);
        self::assertSame($this->data->typeProject->getId(), $project->getType()?->getId());
    }

    public function testEmptyNameIsAFormError(): void
    {
        $this->client->request('GET', '/project/monitoring/new');
        $this->client->submitForm('Ajouter', [
            'project_monitoring[libelle]' => '',
            'project_monitoring[type]' => (string) $this->data->typeProject->getId(),
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function invalidProjects(): iterable
    {
        yield 'name too long' => [['libelle' => str_repeat('a', 256)], 'trop longue'];
        yield 'script link' => [['links' => ['javascript:alert(1)']], 'Cette valeur n\'est pas une URL valide.'];
        // The empty and removed rows leave gaps in the submitted keys: the error must stay under its row.
        yield 'script link after a removed row' => [['links' => [0 => '', 2 => 'javascript:alert(1)']], 'Cette valeur n\'est pas une URL valide.'];
    }

    /**
     * @param array<string, mixed> $fields
     */
    #[DataProvider('invalidProjects')]
    public function testInvalidProjectIsAFormError(array $fields, string $message): void
    {
        $this->postNewProject($fields);

        self::assertResponseStatusCodeSame(422);
        self::assertAnySelectorTextContains('[data-slot="form-message"]', $message);
        self::assertSame(1, $this->em()->getRepository(ProjectMonitoring::class)->count(['owner' => $this->data->user]));
    }

    public function testEmptyLinksAreDropped(): void
    {
        $this->postNewProject(['links' => ['', 'https://a.example.com/x']]);

        self::assertResponseRedirects('/project/monitoring/');
        $project = $this->em()->getRepository(ProjectMonitoring::class)->findOneBy(['owner' => $this->data->user, 'libelle' => 'New project']);
        self::assertSame(['https://a.example.com/x'], $project?->getLinks());
    }

    public function testStoredNonHttpLinkIsNotALink(): void
    {
        // Saved before the links were validated.
        $this->data->project->setLinks(['javascript:alert(1)']);
        $this->em()->flush();

        $this->client->request('GET', '/project/monitoring/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'javascript:alert(1)');
        self::assertSelectorNotExists('a[href^="javascript:"]');
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function postNewProject(array $fields): void
    {
        $crawler = $this->client->request('GET', '/project/monitoring/new');
        $values = $crawler->selectButton('Ajouter')->form()->getPhpValues();
        $values['project_monitoring'] = $fields + [
            'libelle' => 'New project',
            'type' => (string) $this->data->typeProject->getId(),
        ] + $values['project_monitoring'];

        $this->client->request('POST', '/project/monitoring/new', $values);
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
