<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Visitor;

use Aubes\ShadowLoggerBundle\Logger\TransformerException;
use Aubes\ShadowLoggerBundle\Visitor\PropertyAccessorVisitor;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\PropertyAccess\PropertyAccessorBuilder;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class PropertyAccessorVisitorTest extends TestCase
{
    use ProphecyTrait;

    public function testFieldExist()
    {
        $visitor = new PropertyAccessorVisitor((new PropertyAccessorBuilder())->getPropertyAccessor());

        $record = ['field' => 'data'];

        $this->assertTrue($visitor->has($record, '[field]'));
        $this->assertSame('data', $visitor->get($record, '[field]'));

        $visitor->set($record, '[field]', 'data-change');
        $this->assertSame('data-change', $visitor->get($record, '[field]'));
    }

    public function testFieldNotExist()
    {
        $visitor = new PropertyAccessorVisitor((new PropertyAccessorBuilder())->getPropertyAccessor());

        $record = [];

        $this->assertFalse($visitor->has($record, '[field]'));
    }

    public function testGetException()
    {
        $accessor = $this->prophesize(PropertyAccessorInterface::class);
        $accessor->getValue(Argument::any(), Argument::any())->willThrow(\RuntimeException::class);

        $visitor = new PropertyAccessorVisitor($accessor->reveal());

        $record = [];

        $this->assertFalse($visitor->has($record, '[field]'));
    }

    public function testSetException()
    {
        $accessor = $this->prophesize(PropertyAccessorInterface::class);
        $accessor->setValue(Argument::any(), Argument::any(), Argument::any())->willThrow(\RuntimeException::class);

        $visitor = new PropertyAccessorVisitor($accessor->reveal());

        $record = [];

        $this->expectException(TransformerException::class);
        $visitor->set($record, '[field]', 'value');
    }
}
