<?php

namespace App\Tests\Shared\Twig;

use App\Shared\Twig\AmountExtension;
use PHPUnit\Framework\TestCase;

final class AmountExtensionTest extends TestCase
{
    public function testFrenchFormats(): void
    {
        $extension = new AmountExtension();

        self::assertSame("12\u{202F}346\u{00A0}€", $extension->amount(12345.6, '€'));
        self::assertSame("0,1235\u{00A0}$", $extension->amount(0.12345, '$', 4));
        self::assertSame("1\u{202F}234,50", $extension->decimal(1234.5, 2));
        self::assertSame("+1\u{202F}234\u{00A0}€", $extension->signedAmount(1234, '€'));
        self::assertSame("\u{2212}50\u{00A0}€", $extension->signedAmount(-50, '€'));
        self::assertSame("0\u{00A0}€", $extension->signedAmount(-0.4, '€'));
        self::assertSame("2,5\u{00A0}%", $extension->percent(2.54));
        self::assertSame("\u{2212}2,5\u{00A0}%", $extension->signedPercent(-2.54));
        self::assertSame("+12\u{00A0}%", $extension->signedPercent(12.3, 0));
    }
}
