<?php

namespace App\Tests\Admin\Controller;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CryptocurrencyCrudControllerTest extends WebTestCase
{
    public function testDetailPage(): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->admin);

        $client->request('GET', "/admin/cryptocurrency/{$data->btc->getId()}");

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Bitcoin');
        // Cryptocurrency has no EUR price nor market cap.
        self::assertSelectorTextNotContains('body', 'Price Eur');
        self::assertSelectorTextNotContains('body', 'Mcap Eur');
    }
}
