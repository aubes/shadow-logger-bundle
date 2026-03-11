<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Transformer;

use Aubes\ShadowLoggerBundle\Encryptor\EncryptorInterface;
use Aubes\ShadowLoggerBundle\Transformer\EncryptTransformer;
use PHPUnit\Framework\TestCase;

class EncryptTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $encryptor = $this->createStub(EncryptorInterface::class);
        $encryptor->method('generateIv')->willReturn('initialVector');
        $encryptor->method('encrypt')->willReturn('encrypted');

        $transformer = new EncryptTransformer($encryptor);

        $result = $transformer->transform('data');

        $this->assertArrayHasKey('iv', $result);
        $this->assertSame('initialVector', $result['iv']);

        $this->assertArrayHasKey('value', $result);
        $this->assertSame('encrypted', $result['value']);
    }

    public function testTransformNull(): void
    {
        $encryptor = $this->createStub(EncryptorInterface::class);

        $transformer = new EncryptTransformer($encryptor);
        $this->assertSame([], $transformer->transform(null));
    }

    public function testTransformEmptyString(): void
    {
        $encryptor = $this->createMock(EncryptorInterface::class);
        $encryptor->method('generateIv')->willReturn('initialVector');
        $encryptor->expects($this->once())->method('encrypt')->with('', 'initialVector')->willReturn('encrypted');

        $transformer = new EncryptTransformer($encryptor);
        $result = $transformer->transform('');

        $this->assertSame('initialVector', $result['iv']);
        $this->assertSame('encrypted', $result['value']);
    }

    public function testTransformNotScalar(): void
    {
        $encryptor = $this->createStub(EncryptorInterface::class);

        $transformer = new EncryptTransformer($encryptor);

        $this->expectException(\InvalidArgumentException::class);
        $this->assertSame([], $transformer->transform(['data']));
    }
}
