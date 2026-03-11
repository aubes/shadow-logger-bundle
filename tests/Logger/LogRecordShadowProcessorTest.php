<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Logger;

use Aubes\ShadowLoggerBundle\Logger\DataTransformer;
use Aubes\ShadowLoggerBundle\Logger\LogRecordShadowProcessor;
use Aubes\ShadowLoggerBundle\Transformer\TransformerInterface;
use Aubes\ShadowLoggerBundle\Visitor\LoggerVisitorInterface;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

class LogRecordShadowProcessorTest extends TestCase
{
    public function testProcessor(): void
    {
        $processor = new LogRecordShadowProcessor(false);

        $visitor = $this->createStub(LoggerVisitorInterface::class);
        $visitor->method('has')->willReturn(false);

        $dataTransformer = new DataTransformer('field', $visitor, [], true);
        $processor->addDataTransformer('context', $dataTransformer);

        $record = new LogRecord(new \DateTimeImmutable(), 'app', Level::Info, 'message');

        $this->assertInstanceOf(LogRecord::class, $record);
        $this->assertSame([], $record->context);
        $this->assertSame([], $record->extra);
    }

    public function testProcessorDebug(): void
    {
        $processor = new LogRecordShadowProcessor(true);

        $visitor = $this->createStub(LoggerVisitorInterface::class);
        $visitor->method('has')->willReturn(true);
        $visitor->method('get')->willReturn('data');

        $innerTransformer = $this->createStub(TransformerInterface::class);
        $innerTransformer->method('transform')->willThrowException(new \Exception('Message'));

        $dataTransformer = new DataTransformer('field', $visitor, [$innerTransformer], true);
        $processor->addDataTransformer('context', $dataTransformer);

        $record = new LogRecord(new \DateTimeImmutable(), 'app', Level::Info, 'message');
        $record = $processor($record);

        $this->assertArrayHasKey('shadow-debug', $record->extra);
        $this->assertSame('context', $record->extra['shadow-debug']['property']);
        $this->assertSame('field', $record->extra['shadow-debug']['field']);
        $this->assertSame('Message', $record->extra['shadow-debug']['message']);
    }
}
