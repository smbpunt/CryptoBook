<?php

namespace App\Tests\Strategy\Dca\Generator;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Position\Entity\Position;
use App\Strategy\Dca\Enum\DcaRecurrence;
use App\Strategy\Dca\Generator\DcaAutoPositionGenerator;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class DcaAutoPositionGeneratorTest extends TestCase
{
    /**
     * Dates themselves are covered by DcaRecurrenceTest.
     */
    public function testSpreadsTheTotalOverTheDates(): void
    {
        $coin = new Cryptocurrency();

        $positions = new DcaAutoPositionGenerator()->generate(new User(), $coin, DcaRecurrence::Weekly, 3, 300, new \DateTimeImmutable('2024-01-31 10:00'));

        self::assertSame(['2024-01-31 10:00', '2024-02-07 10:00', '2024-02-14 10:00'], array_map(static fn (Position $p): string => (string) $p->getOpenedAt()?->format('Y-m-d H:i'), $positions));
        foreach ($positions as $position) {
            self::assertSame($coin, $position->getCoin());
            self::assertSame(100.0, $position->getEntryCost());
            self::assertTrue($position->isIsDca());
        }
    }
}
