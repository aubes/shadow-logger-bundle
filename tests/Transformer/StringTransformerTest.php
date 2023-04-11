<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Transformer;

use Aubes\ShadowLoggerBundle\Transformer\StringTransformer;
use PHPUnit\Framework\TestCase;

class StringTransformerTest extends TestCase
{
    public function testTransform()
    {
        $transformer = new StringTransformer();
        $this->assertSame('data', $transformer->transform('data'));
        $this->assertSame('', $transformer->transform(''));
        $this->assertSame('', $transformer->transform(null));
        $this->assertSame('123', $transformer->transform(123));
        $this->assertSame('1.23', $transformer->transform(1.23));
        $this->assertSame('1', $transformer->transform(true));
        $this->assertSame('Stringable, I am', $transformer->transform(new class() {
            public function __toString()
            {
                return 'Stringable, I am';
            }
        }));
    }

    public function testTransformNotStringable()
    {
        $transformer = new StringTransformer();

        $this->expectException(\InvalidArgumentException::class);
        $this->assertSame('', $transformer->transform(['data']));
    }
}
