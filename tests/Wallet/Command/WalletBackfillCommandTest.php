<?php

namespace App\Tests\Wallet\Command;

use App\Tests\Fixtures\Dataset;
use App\Wallet\Repository\WalletSnapshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

final class WalletBackfillCommandTest extends KernelTestCase
{
    use ClockSensitiveTrait;

    private MockHttpClient $http;

    protected function setUp(): void
    {
        self::mockTime('2026-11-10 14:35:00 UTC');
        Dataset::load(self::getContainer()->get(EntityManagerInterface::class));
        $http = self::getContainer()->get('http_client.transport');
        \assert($http instanceof MockHttpClient);
        $this->http = $http;
    }

    public function testBackfillsTheHistory(): void
    {
        $this->http->setResponseFactory(new JsonMockResponse(['prices' => [[1791892800000, 60000]]]));

        $result = self::runCommand('wallet:backfill');

        self::assertCommandIsSuccessful($result);
        self::assertStringContainsString('720 snapshot(s) backfilled for 1 user(s), 1 coin(s) fetched.', $result->getDisplay());
    }

    public function testCoingeckoFailureWritesNothing(): void
    {
        $this->http->setResponseFactory(new MockResponse('', ['http_code' => 500]));

        $result = self::runCommand('wallet:backfill');

        self::assertSame(Command::FAILURE, $result->statusCode);
        self::assertStringContainsString('Nothing written', $result->getDisplay());
        self::assertSame(0, self::getContainer()->get(WalletSnapshotRepository::class)->count());
    }
}
