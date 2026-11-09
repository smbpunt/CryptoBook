<?php

namespace App\Tests\Functional;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A user cannot edit the data of another user.
 */
final class OwnershipTest extends WebTestCase
{
    public function testCannotEditDataOfAnotherUser(): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->admin);

        $pages = [
            '/deposit/%d/edit' => [$data->deposit->getId(), '/deposit/'],
            '/loan/%d/edit' => [$data->loan->getId(), '/loan/'],
            '/nft/%d/edit' => [$data->nft->getId(), '/nft/'],
            '/position/%d/edit' => [$data->position->getId(), '/position/'],
            '/project/monitoring/%d/edit' => [$data->project->getId(), '/project/monitoring/'],
            '/strategy/farming/%d/edit' => [$data->farming->getId(), '/strategy/farming/'],
            '/strategy/lp/%d/edit' => [$data->lp->getId(), '/strategy/farming/'],
        ];

        foreach ($pages as $url => [$id, $index]) {
            $client->request('GET', \sprintf($url, $id));
            self::assertResponseRedirects($index, message: $url);
        }
    }
}
