<?php

namespace App\Tests\Functional;

use App\Entity\OwnedInterface;
use App\Entity\User;
use App\Repository\DepositRepository;
use App\Repository\StrategyDcaRepository;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A user cannot read, edit or delete the data of another user.
 */
final class OwnershipTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function ownedRoutes(): iterable
    {
        foreach ([
            'deposit' => ['/deposit', 'deposit'],
            'loan' => ['/loan', 'loan'],
            'nft' => ['/nft', 'nft'],
            'position' => ['/position', 'position'],
            'project' => ['/project/monitoring', 'project'],
            'farming' => ['/strategy/farming', 'farming'],
            'lp' => ['/strategy/lp', 'lp'],
        ] as $name => [$prefix, $property]) {
            yield "$name edit" => ['GET', "$prefix/{id}/edit", $property];
            yield "$name delete" => ['POST', "$prefix/{id}", $property];
        }

        foreach (['loan' => '/loan', 'nft' => '/nft', 'position' => '/position', 'farming' => '/strategy/farming', 'lp' => '/strategy/lp'] as $property => $prefix) {
            yield "$property infos" => ['POST', "$prefix/{id}/infos", $property];
        }

        yield 'dca delete' => ['POST', '/strategy/dca/{id}', 'dca'];
    }

    #[DataProvider('ownedRoutes')]
    public function testCannotAccessDataOfAnotherUser(string $method, string $path, string $property): void
    {
        $client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $data = Dataset::load($em);
        $entity = $data->{$property};
        \assert($entity instanceof OwnedInterface && method_exists($entity, 'getId'));
        $client->loginUser($data->admin);

        $client->request($method, str_replace('{id}', (string) $entity->getId(), $path), ['_token' => 'whatever']);

        self::assertResponseStatusCodeSame(403);
        $em->clear();
        self::assertNotNull($em->find($entity::class, $entity->getId()));
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

    public function testDeletingTheDcaStrategyKeepsItsOwner(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $data = Dataset::load($em);
        $userId = $data->user->getId();
        $em->clear();

        $repository = self::getContainer()->get(StrategyDcaRepository::class);
        $repository->remove($repository->findOneBy(['owner' => $userId]), true);
        $em->clear();

        self::assertNull($repository->findOneBy(['owner' => $userId]));
        self::assertNotNull($em->find(User::class, $userId));
    }
}
