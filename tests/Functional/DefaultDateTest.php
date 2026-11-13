<?php

namespace App\Tests\Functional;

use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

/**
 * The creation forms propose today's date, the edit forms keep the stored one.
 */
final class DefaultDateTest extends WebTestCase
{
    use ClockSensitiveTrait;

    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        self::mockTime('2026-03-10 12:00 UTC');
        $this->client = self::createClient();
        $this->data = Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $this->client->loginUser($this->data->user);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function creationForms(): iterable
    {
        yield 'position' => ['/position/new', 'position[openedAt]'];
        yield 'deposit' => ['/deposit/new', 'deposit[depositedAt]'];
        yield 'loan' => ['/loan/new', 'loan[loanedAt]'];
        yield 'nft' => ['/nft/new', 'nft[purchasedOn]'];
        yield 'farming' => ['/strategy/farming/new', 'strategy_farming[enteredAt]'];
        yield 'lp' => ['/strategy/lp/new', 'strategy_lp[startAt]'];
    }

    #[DataProvider('creationForms')]
    public function testCreationFormProposesToday(string $url, string $field): void
    {
        $this->client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertInputValueSame($field, '2026-03-10');
    }

    public function testAddedSaleProposesToday(): void
    {
        $crawler = $this->client->request('GET', '/position/new');

        self::assertStringContainsString('2026-03-10', (string) $crawler->filter('[data-collection-prototype-value*="soldAt"]')->attr('data-collection-prototype-value'));
    }

    public function testEditFormKeepsAnEmptyDate(): void
    {
        $this->client->request('GET', "/loan/{$this->data->loan->getId()}/edit");

        self::assertInputValueSame('loan[loanedAt]', '');
    }

    public function testSaleDateOfANftIsNotProposed(): void
    {
        $this->client->request('GET', '/nft/new');

        self::assertInputValueSame('nft[soldOn]', '');
    }
}
