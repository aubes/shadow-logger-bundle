<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Transformer;

use Aubes\ShadowLoggerBundle\Transformer\IpTransformer;
use PHPUnit\Framework\TestCase;

class IpTransformerTest extends TestCase
{
    public function testTransform()
    {
        $transformer = new IpTransformer();
        $this->assertSame('123.234.235.0', $transformer->transform('123.234.235.236'));
        $this->assertSame('2a01:198:603:10::', $transformer->transform('2a01:198:603:10:396e:4789:8e99:890f'));
    }

    public function testTransformNotString()
    {
        $transformer = new IpTransformer();

        $this->expectException(\InvalidArgumentException::class);

        $this->assertSame('', $transformer->transform(''));
        $this->assertSame('', $transformer->transform(null));
        $this->assertSame('', $transformer->transform(['data']));
    }
}
