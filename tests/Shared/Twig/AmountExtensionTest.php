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
        self::assertSame("3\u{202F}012\u{00A0}$", $extension->price(3012.4, '$'));
        self::assertSame("12,35\u{00A0}$", $extension->price(12.345, '$'));
        self::assertSame("0,4512\u{00A0}$", $extension->price(0.45123, '$'));
        self::assertSame("0,000008912\u{00A0}$", $extension->price(0.0000089123, '$'));
        self::assertSame("1\u{202F}000\u{00A0}$", $extension->price(999.996, '$'));
        self::assertSame("1,00\u{00A0}$", $extension->price(0.99996, '$'));
        self::assertSame("0,00\u{00A0}$", $extension->price(0, '$'));
        self::assertSame('0,184', $extension->quantity(0.184));
        self::assertSame('0,00001235', $extension->quantity(0.0000123456));
        self::assertSame('3,0612', $extension->quantity(3.06123));
        self::assertSame("1\u{202F}251", $extension->quantity(1250.6));
        self::assertSame('2', $extension->quantity(2.0));
    }
}
