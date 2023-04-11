<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Transformer;

use Aubes\ShadowLoggerBundle\Encoder\EncoderInterface;
use Aubes\ShadowLoggerBundle\Transformer\HashTransformer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class HashTransformerTest extends TestCase
{
    use ProphecyTrait;

    public function testTransform()
    {
        $encoder = $this->prophesize(EncoderInterface::class);
        $encoder->hash(Argument::any())->willReturn('encoded');

        $transformer = new HashTransformer($encoder->reveal());
        $this->assertSame('encoded', $transformer->transform('data'));
        $this->assertSame('encoded', $transformer->transform(123));
        $this->assertSame('encoded', $transformer->transform(1.23));
        $this->assertSame('encoded', $transformer->transform(true));
    }

    public function testTransformEmpty()
    {
        $encoder = $this->prophesize(EncoderInterface::class);

        $transformer = new HashTransformer($encoder->reveal());
        $this->assertSame('', $transformer->transform(''));
        $this->assertSame('', $transformer->transform(null));
    }

    public function testTransformNotScalar()
    {
        $encoder = $this->prophesize(EncoderInterface::class);

        $transformer = new HashTransformer($encoder->reveal());

        $this->expectException(\InvalidArgumentException::class);
        $this->assertSame('', $transformer->transform(['data']));
    }
}
