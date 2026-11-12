<?php

namespace App\Tests\Shared\Twig;

use App\Shared\Twig\DateExtension;
use PHPUnit\Framework\TestCase;

final class DateExtensionTest extends TestCase
{
    public function testFrenchDay(): void
    {
        $extension = new DateExtension();

        self::assertSame('10 nov. 2026', $extension->day(new \DateTimeImmutable('2026-11-10 23:30', new \DateTimeZone('Europe/Paris'))));
        self::assertSame('3 févr. 2025', $extension->day(new \DateTimeImmutable('2025-02-03')));
        self::assertSame('', $extension->day(null));
    }
}
