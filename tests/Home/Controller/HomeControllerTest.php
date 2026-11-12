<?php

namespace App\Tests\Home\Controller;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testLoggedInUserIsRedirectedToTheDashboard(): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->user);

        $client->request('GET', '/');

        self::assertResponseRedirects('/dashboard');
    }
}
