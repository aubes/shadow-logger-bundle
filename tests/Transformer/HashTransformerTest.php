<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Transformer;

use Aubes\ShadowLoggerBundle\Encoder\EncoderInterface;
use Aubes\ShadowLoggerBundle\Transformer\HashTransformer;
use PHPUnit\Framework\TestCase;

class HashTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $encoder = $this->createStub(EncoderInterface::class);
        $encoder->method('hash')->willReturn('encoded');

        $transformer = new HashTransformer($encoder);
        $this->assertSame('encoded', $transformer->transform('data'));
        $this->assertSame('encoded', $transformer->transform(123));
        $this->assertSame('encoded', $transformer->transform(1.23));
        $this->assertSame('encoded', $transformer->transform(true));
    }

    public function testTransformNull(): void
    {
        $encoder = $this->createStub(EncoderInterface::class);

        $transformer = new HashTransformer($encoder);
        $this->assertSame('', $transformer->transform(null));
    }

    public function testTransformEmptyString(): void
    {
        $encoder = $this->createMock(EncoderInterface::class);
        $encoder->expects($this->once())->method('hash')->with('')->willReturn('encoded');

        $transformer = new HashTransformer($encoder);
        $this->assertSame('encoded', $transformer->transform(''));
    }

    public function testTransformNotScalar(): void
    {
        $encoder = $this->createStub(EncoderInterface::class);

        $transformer = new HashTransformer($encoder);

        $this->expectException(\InvalidArgumentException::class);
        $this->assertSame('', $transformer->transform(['data']));
    }
}
