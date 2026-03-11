<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Truncator;

use Aubes\ShadowLoggerBundle\Truncator\Truncator;
use PHPUnit\Framework\TestCase;

class TruncatorTest extends TestCase
{
    public function testTruncate(): void
    {
        $truncator = new Truncator(2, 2, '***');
        $this->assertSame('jo***om', $truncator->truncate('john@example.com'));
    }

    public function testTruncateNoEnd(): void
    {
        $truncator = new Truncator(4, 0, '****');
        $this->assertSame('4242****', $truncator->truncate('4242424242424242'));
    }

    public function testTruncateNoStart(): void
    {
        $truncator = new Truncator(0, 4, '****');
        $this->assertSame('****4242', $truncator->truncate('4242424242424242'));
    }

    public function testTruncateBothEnds(): void
    {
        $truncator = new Truncator(4, 4, '****');
        $this->assertSame('4242****4242', $truncator->truncate('4242424242424242'));
    }

    public function testTruncateShortString(): void
    {
        $truncator = new Truncator(2, 2, '***');
        $this->assertSame('***', $truncator->truncate('ab'));
        $this->assertSame('***', $truncator->truncate('abc'));
    }

    public function testTruncateCustomMask(): void
    {
        $truncator = new Truncator(1, 0, '--hidden--');
        $this->assertSame('j--hidden--', $truncator->truncate('john'));
    }
}
