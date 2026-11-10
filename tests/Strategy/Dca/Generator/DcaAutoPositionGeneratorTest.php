<?php

namespace App\Tests\Strategy\Dca\Generator;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Position\Entity\Position;
use App\Strategy\Dca\Enum\DcaRecurrence;
use App\Strategy\Dca\Generator\DcaAutoPositionGenerator;
use App\User\Entity\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DcaAutoPositionGeneratorTest extends TestCase
{
    /**
     * @return iterable<string, array{DcaRecurrence, list<string>}>
     */
    public static function recurrences(): iterable
    {
        yield 'four hours' => [DcaRecurrence::FourHours, ['2024-01-31 10:00', '2024-01-31 14:00', '2024-01-31 18:00']];
        yield 'daily' => [DcaRecurrence::Daily, ['2024-01-31 10:00', '2024-02-01 10:00', '2024-02-02 10:00']];
        yield 'weekly' => [DcaRecurrence::Weekly, ['2024-01-31 10:00', '2024-02-07 10:00', '2024-02-14 10:00']];
        // Same day of the month as the first purchase, or the last day of shorter months.
        yield 'monthly' => [DcaRecurrence::Monthly, ['2024-01-31 10:00', '2024-02-29 10:00', '2024-03-31 10:00']];
    }

    /**
     * @param list<string> $expectedDates
     */
    #[DataProvider('recurrences')]
    public function testSpreadsTheTotalOverTheDates(DcaRecurrence $recurrence, array $expectedDates): void
    {
        $coin = new Cryptocurrency();

        $positions = new DcaAutoPositionGenerator()->generate(new User(), $coin, $recurrence, 3, 300, new \DateTimeImmutable('2024-01-31 10:00'));

        self::assertSame($expectedDates, array_map(static fn (Position $p): string => (string) $p->getOpenedAt()?->format('Y-m-d H:i'), $positions));
        foreach ($positions as $position) {
            self::assertSame($coin, $position->getCoin());
            self::assertSame(100.0, $position->getEntryCost());
            self::assertTrue($position->isIsDca());
        }
    }
}
