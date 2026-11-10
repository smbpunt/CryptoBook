<?php

namespace App\Tests\ProjectMonitoring\Controller;

use App\ProjectMonitoring\Entity\ProjectMonitoring;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
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

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
