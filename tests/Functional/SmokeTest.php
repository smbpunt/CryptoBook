<?php

namespace App\Tests\Functional;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Every GET page renders for its audience.
 */
final class SmokeTest extends WebTestCase
{
    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function publicUrls(): iterable
    {
        yield 'login' => ['/login'];
        yield 'register' => ['/register'];
        yield 'reset password' => ['/reset-password'];
    }

    #[DataProvider('publicUrls')]
    public function testPublicPages(string $url): void
    {
        $this->client->request('GET', $url);

        self::assertResponseIsSuccessful();
    }

    public function testAnonymousIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/position/');

        self::assertResponseRedirects('/login');
    }

    public function testUserPages(): void
    {
        $d = $this->data;
        $this->client->loginUser($d->user);

        $urls = [
            '/',
            '/deposit/', '/deposit/new', "/deposit/{$d->deposit->getId()}/edit",
            '/loan/', '/loan/new', "/loan/{$d->loan->getId()}/edit",
            '/nft/', '/nft/new', "/nft/{$d->nft->getId()}/edit",
            '/position/', '/position/new', "/position/{$d->position->getId()}/edit",
            '/project/monitoring/', '/project/monitoring/new', "/project/monitoring/{$d->project->getId()}/edit",
            '/strategy/dca/', '/strategy/dca/edit', '/strategy/dca/generatePositions', '/strategy/dca/generatePositions/150.5',
            '/strategy/farming/', '/strategy/farming/new', "/strategy/farming/{$d->farming->getId()}/edit",
            '/strategy/lp/new', "/strategy/lp/{$d->lp->getId()}/edit",
            '/user/preferences',
        ];

        foreach ($urls as $url) {
            $this->client->request('GET', $url);
            self::assertResponseIsSuccessful("GET $url");
        }
    }

    public function testAjaxInfos(): void
    {
        $d = $this->data;
        $this->client->loginUser($d->user);

        foreach ([
            "/loan/{$d->loan->getId()}/infos",
            "/nft/{$d->nft->getId()}/infos",
            "/position/{$d->position->getId()}/infos",
            "/strategy/farming/{$d->farming->getId()}/infos",
            "/strategy/lp/{$d->lp->getId()}/infos",
        ] as $url) {
            $this->client->request('POST', $url);
            self::assertResponseIsSuccessful("POST $url");
        }
    }

    public function testAjaxInfosOfAnotherUserIsForbidden(): void
    {
        $this->client->loginUser($this->data->admin);
        $this->client->request('POST', "/position/{$this->data->position->getId()}/infos");

        self::assertResponseStatusCodeSame(403);
    }

    public function testDcaAuto(): void
    {
        $this->client->loginUser($this->data->user);

        $this->client->request('GET', '/strategy/dca/dcaAuto?'.http_build_query([
            'crypto' => $this->data->btc->getId(), 't' => 'w', 'nb' => 3, 'total' => 300, 'd' => '2024-01-01',
        ]));
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(3, 'input[name$="[entryCost]"]');

        $this->client->request('GET', '/strategy/dca/dcaAuto');
        self::assertResponseRedirects('/strategy/dca/');

        $this->client->request('GET', '/strategy/dca/dcaAuto?crypto=1&t=yearly&nb=3&total=300&d=2024-01-01');
        self::assertResponseStatusCodeSame(404);
    }

    public function testDcaNewRedirectsToEditWhenStrategyExists(): void
    {
        $this->client->loginUser($this->data->user);
        $this->client->request('GET', '/strategy/dca/new');

        self::assertResponseRedirects('/strategy/dca/edit');
    }

    public function testUserCannotAccessAdmin(): void
    {
        $this->client->loginUser($this->data->user);
        $this->client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminPages(): void
    {
        $this->client->loginUser($this->data->admin);

        $this->client->request('GET', '/admin');
        self::assertResponseRedirects();

        $entities = [
            'blockchain' => $this->data->ethereum->getId(),
            'cryptocurrency' => $this->data->btc->getId(),
            'dapp' => $this->data->aave->getId(),
            'deposit-type' => $this->data->depositType->getId(),
            'exchange' => $this->data->exchange->getId(),
            'fiat-currency' => $this->data->usd->getId(),
            'type-project' => $this->data->typeProject->getId(),
            'user' => $this->data->user->getId(),
        ];

        foreach ($entities as $entity => $id) {
            foreach (["/admin/$entity", "/admin/$entity/new", "/admin/$entity/$id/edit"] as $url) {
                $this->client->request('GET', $url);
                self::assertResponseIsSuccessful($url);
            }
        }
    }
}
