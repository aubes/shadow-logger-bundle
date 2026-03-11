<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Transformer;

use Aubes\ShadowLoggerBundle\Transformer\TruncateTransformer;
use Aubes\ShadowLoggerBundle\Truncator\TruncatorInterface;
use PHPUnit\Framework\TestCase;

class TruncateTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $truncator = $this->createMock(TruncatorInterface::class);
        $truncator->expects($this->once())->method('truncate')->with('data')->willReturn('da***ta');

        $transformer = new TruncateTransformer($truncator);
        $this->assertSame('da***ta', $transformer->transform('data'));
    }

    public function testTransformScalar(): void
    {
        $truncator = $this->createStub(TruncatorInterface::class);
        $truncator->method('truncate')->willReturn('***');

        $transformer = new TruncateTransformer($truncator);
        $this->assertSame('***', $transformer->transform(123));
        $this->assertSame('***', $transformer->transform(1.23));
        $this->assertSame('***', $transformer->transform(true));
    }

    public function testTransformNull(): void
    {
        $truncator = $this->createStub(TruncatorInterface::class);

        $transformer = new TruncateTransformer($truncator);
        $this->assertSame('', $transformer->transform(null));
    }

    public function testTransformNotScalar(): void
    {
        $truncator = $this->createStub(TruncatorInterface::class);

        $transformer = new TruncateTransformer($truncator);

        $this->expectException(\InvalidArgumentException::class);
        $transformer->transform(['data']);
    }
}
