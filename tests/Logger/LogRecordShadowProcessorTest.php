<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Logger;

use Aubes\ShadowLoggerBundle\Logger\DataTransformer;
use Aubes\ShadowLoggerBundle\Logger\LogRecordShadowProcessor;
use Aubes\ShadowLoggerBundle\Logger\TransformerException;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class LogRecordShadowProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function testProcessor()
    {
        $processor = new LogRecordShadowProcessor(false);

        $dataTransformer = $this->prophesize(DataTransformer::class);
        $dataTransformer->transform(Argument::any());

        $processor->addDataTransformer('context', $dataTransformer->reveal());

        $record = new LogRecord(new \DateTimeImmutable(), 'app', Level::Info, 'message');

        $this->assertInstanceOf(LogRecord::class, $record);
        $this->assertSame([], $record->context);
        $this->assertSame([], $record->extra);
    }

    public function testProcessorDebug()
    {
        $processor = new LogRecordShadowProcessor(true);

        $exception = new TransformerException('field', 'Message');

        $dataTransformer = $this->prophesize(DataTransformer::class);
        $dataTransformer->transform(Argument::any())->willThrow($exception);

        $processor->addDataTransformer('context', $dataTransformer->reveal());

        $record = new LogRecord(new \DateTimeImmutable(), 'app', Level::Info, 'message');
        $record = $processor($record);

        $this->assertArrayHasKey('shadow-debug', $record->extra);
        $this->assertSame('context', $record->extra['shadow-debug']['property']);
        $this->assertSame('field', $record->extra['shadow-debug']['field']);
        $this->assertSame('Message', $record->extra['shadow-debug']['message']);
    }
}
