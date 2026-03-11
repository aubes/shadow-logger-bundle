<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Visitor;

use Aubes\ShadowLoggerBundle\Logger\TransformerException;
use Aubes\ShadowLoggerBundle\Visitor\PropertyAccessorVisitor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessorBuilder;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class PropertyAccessorVisitorTest extends TestCase
{
    public function testFieldExist(): void
    {
        $visitor = new PropertyAccessorVisitor((new PropertyAccessorBuilder())->getPropertyAccessor());

        $record = ['field' => 'data'];

        $this->assertTrue($visitor->has($record, '[field]'));
        $this->assertSame('data', $visitor->get($record, '[field]'));

        $visitor->set($record, '[field]', 'data-change');
        $this->assertSame('data-change', $visitor->get($record, '[field]'));
    }

    public function testFieldNotExist(): void
    {
        $visitor = new PropertyAccessorVisitor((new PropertyAccessorBuilder())->getPropertyAccessor());

        $record = [];

        $this->assertFalse($visitor->has($record, '[field]'));
    }

    public function testGetException(): void
    {
        $accessor = $this->createStub(PropertyAccessorInterface::class);
        $accessor->method('getValue')->willThrowException(new \RuntimeException());

        $visitor = new PropertyAccessorVisitor($accessor);

        $record = [];

        $this->assertFalse($visitor->has($record, '[field]'));
    }

    public function testSetException(): void
    {
        $accessor = $this->createStub(PropertyAccessorInterface::class);
        $accessor->method('setValue')->willThrowException(new \RuntimeException());

        $visitor = new PropertyAccessorVisitor($accessor);

        $record = [];

        $this->expectException(TransformerException::class);
        $visitor->set($record, '[field]', 'value');
    }
}
