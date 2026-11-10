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

    public function testInterval(): void
    {
        $date = new \DateTimeImmutable('2024-01-31 10:00');

        self::assertSame('2024-01-31 14:00', $date->add(DcaRecurrence::FourHours->interval())->format('Y-m-d H:i'));
        self::assertSame('2024-02-07 10:00', $date->add(DcaRecurrence::Weekly->interval())->format('Y-m-d H:i'));
    }
}
