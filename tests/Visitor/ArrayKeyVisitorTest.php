<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Visitor;

use Aubes\ShadowLoggerBundle\Visitor\ArrayKeyVisitor;
use PHPUnit\Framework\TestCase;

class ArrayKeyVisitorTest extends TestCase
{
    public function testFieldExist()
    {
        $visitor = new ArrayKeyVisitor();

        $record = ['field' => 'data'];

        $this->assertTrue($visitor->has($record, 'field'));
        $this->assertSame('data', $visitor->get($record, 'field'));

        $visitor->set($record, 'field', 'data-change');
        $this->assertSame('data-change', $visitor->get($record, 'field'));
    }

    public function testFieldNotExist()
    {
        $visitor = new ArrayKeyVisitor();

        $record = [];

        $this->assertFalse($visitor->has($record, 'field'));
    }
}
