<?php

namespace App\Tests\Functional;

use App\Repository\DepositRepository;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A user cannot read, edit or delete the data of another user.
 */
final class OwnershipTest extends WebTestCase
{
    public function testCannotEditDataOfAnotherUser(): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->admin);

        foreach ([
            "/deposit/{$data->deposit->getId()}/edit",
            "/loan/{$data->loan->getId()}/edit",
            "/nft/{$data->nft->getId()}/edit",
            "/position/{$data->position->getId()}/edit",
            "/project/monitoring/{$data->project->getId()}/edit",
            "/strategy/farming/{$data->farming->getId()}/edit",
            "/strategy/lp/{$data->lp->getId()}/edit",
        ] as $url) {
            $client->request('GET', $url);
            self::assertResponseStatusCodeSame(403, $url);
        }
    }

    public function testCannotDeleteDataOfAnotherUser(): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->admin);

        $client->request('POST', "/deposit/{$data->deposit->getId()}", ['_token' => 'whatever']);

        self::assertResponseStatusCodeSame(403);
        self::assertNotNull(self::getContainer()->get(DepositRepository::class)->find($data->deposit->getId()));
    }

    public function testOwnerDeletesWithAValidCsrfToken(): void
    {
        $client = self::createClient();
        $data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $client->loginUser($data->user);
        $id = $data->deposit->getId();

        // An invalid token is an authentication error: the firewall sends the user to the login page.
        $client->request('POST', "/deposit/$id", ['_token' => 'invalid']);
        self::assertResponseRedirects('/login');
        self::assertNotNull(self::getContainer()->get(DepositRepository::class)->find($id));

        $client->request('GET', '/deposit/');
        $client->submit($client->getCrawler()->filter(\sprintf('form[action="/deposit/%d"]', $id))->form());

        self::assertResponseRedirects('/deposit/');
        self::assertNull(self::getContainer()->get(DepositRepository::class)->find($id));
    }
}
