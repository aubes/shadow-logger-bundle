<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Transformer;

use Aubes\ShadowLoggerBundle\Encryptor\EncryptorInterface;
use Aubes\ShadowLoggerBundle\Transformer\EncryptTransformer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class EncryptTransformerTest extends TestCase
{
    use ProphecyTrait;

    public function testTransform()
    {
        $encryptor = $this->prophesize(EncryptorInterface::class);
        $encryptor->encrypt(Argument::any(), 'initialVector')->willReturn('encrypted');
        $encryptor->generateIv()->willReturn('initialVector');

        $transformer = new EncryptTransformer($encryptor->reveal());

        $result = $transformer->transform('data');

        $this->assertArrayHasKey('iv', $result);
        $this->assertSame('initialVector', $result['iv']);

        $this->assertArrayHasKey('value', $result);
        $this->assertSame('encrypted', $result['value']);
    }

    public function testTransformEmpty()
    {
        $encryptor = $this->prophesize(EncryptorInterface::class);

        $transformer = new EncryptTransformer($encryptor->reveal());
        $this->assertSame([], $transformer->transform(''));
        $this->assertSame([], $transformer->transform(null));
    }

    public function testTransformNotScalar()
    {
        $encryptor = $this->prophesize(EncryptorInterface::class);

        $transformer = new EncryptTransformer($encryptor->reveal());

        $this->expectException(\InvalidArgumentException::class);
        $this->assertSame([], $transformer->transform(['data']));
    }
}
