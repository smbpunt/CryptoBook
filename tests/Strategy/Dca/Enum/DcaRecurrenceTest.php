<?php

namespace App\Tests\Strategy\Dca\Enum;

use App\Strategy\Dca\Enum\DcaRecurrence;
use PHPUnit\Framework\TestCase;

final class DcaRecurrenceTest extends TestCase
{
    public function testValuesOfTheQueryString(): void
    {
        self::assertSame(['h', 'd', 'w', 'm'], array_column(DcaRecurrence::cases(), 'value'));
    }

    public function testDateAt(): void
    {
        $first = new \DateTimeImmutable('2024-01-31 10:00');

        self::assertSame('2024-01-31 10:00', DcaRecurrence::Daily->dateAt($first, 0)->format('Y-m-d H:i'));
        self::assertSame('2024-01-31 18:00', DcaRecurrence::FourHours->dateAt($first, 2)->format('Y-m-d H:i'));
        self::assertSame('2024-02-14 10:00', DcaRecurrence::Weekly->dateAt($first, 2)->format('Y-m-d H:i'));
        self::assertSame('2024-02-29 10:00', DcaRecurrence::Monthly->dateAt($first, 1)->format('Y-m-d H:i'));
        self::assertSame('2025-01-31 10:00', DcaRecurrence::Monthly->dateAt($first, 12)->format('Y-m-d H:i'));
        // Across the DST change of 2024-03-31 in Paris.
        $paris = new \DateTimeImmutable('2024-03-30 10:00', new \DateTimeZone('Europe/Paris'));
        self::assertSame('2024-04-01 10:00', DcaRecurrence::Daily->dateAt($paris, 2)->format('Y-m-d H:i'));
    }
}
