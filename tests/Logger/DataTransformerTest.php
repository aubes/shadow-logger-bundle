<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Logger;

use Aubes\ShadowLoggerBundle\Logger\DataTransformer;
use Aubes\ShadowLoggerBundle\Logger\TransformerException;
use Aubes\ShadowLoggerBundle\Transformer\TransformerInterface;
use Aubes\ShadowLoggerBundle\Visitor\LoggerVisitorInterface;
use PHPUnit\Framework\TestCase;

class DataTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $visitor = $this->createMock(LoggerVisitorInterface::class);
        $visitor->expects($this->once())->method('has')->with($this->anything(), 'field')->willReturn(true);
        $visitor->expects($this->once())->method('get')->with($this->anything(), 'field')->willReturn('data');
        $visitor->expects($this->once())->method('set')->with($this->anything(), 'field', 'data-change');

        $transformer = $this->createMock(TransformerInterface::class);
        $transformer->method('transform')->with('data')->willReturn('data-change');

        $record = [];

        $dataTransformer = new DataTransformer('field', $visitor, [$transformer], true);
        $dataTransformer->transform($record);
    }

    public function testTransformFieldNotExist(): void
    {
        $visitor = $this->createMock(LoggerVisitorInterface::class);
        $visitor->expects($this->once())->method('has')->willReturn(false);
        $visitor->expects($this->never())->method('get');
        $visitor->expects($this->never())->method('set');

        $record = [];

        $dataTransformer = new DataTransformer('field', $visitor, [], true);
        $dataTransformer->transform($record);
    }

    public function testTransformExceptionNotStrict(): void
    {
        $visitor = $this->createMock(LoggerVisitorInterface::class);
        $visitor->expects($this->once())->method('has')->willReturn(true);
        $visitor->expects($this->once())->method('get')->willReturn('data');
        $visitor->expects($this->once())->method('set')->with($this->anything(), 'field', 'data');

        $transformer = $this->createStub(TransformerInterface::class);
        $transformer->method('transform')->willThrowException(new \Exception());

        $record = [];

        $dataTransformer = new DataTransformer('field', $visitor, [$transformer], false);

        $this->expectException(TransformerException::class);
        $dataTransformer->transform($record);
    }

    public function testTransformExceptionStrict(): void
    {
        $visitor = $this->createMock(LoggerVisitorInterface::class);
        $visitor->expects($this->once())->method('has')->willReturn(true);
        $visitor->expects($this->once())->method('get')->willReturn('data');
        $visitor->expects($this->once())->method('set')->with($this->anything(), 'field', null);

        $transformer = $this->createStub(TransformerInterface::class);
        $transformer->method('transform')->willThrowException(new \Exception());

        $record = [];

        $dataTransformer = new DataTransformer('field', $visitor, [$transformer], true);

        $this->expectException(TransformerException::class);
        $dataTransformer->transform($record);
    }
}
