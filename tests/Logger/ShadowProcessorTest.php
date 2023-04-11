<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Logger;

use Aubes\ShadowLoggerBundle\Logger\DataTransformer;
use Aubes\ShadowLoggerBundle\Logger\ShadowProcessor;
use Aubes\ShadowLoggerBundle\Logger\TransformerException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class ShadowProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function testProcessor()
    {
        $processor = new ShadowProcessor(false);

        $dataTransformer = $this->prophesize(DataTransformer::class);
        $dataTransformer->transform(Argument::any());

        $processor->addDataTransformer('context', $dataTransformer->reveal());

        $record = ['context' => []];
        $this->assertSame($record, $processor($record));
    }

    public function testProcessorDebug()
    {
        $processor = new ShadowProcessor(true);

        $exception = new TransformerException('field', 'Message');

        $dataTransformer = $this->prophesize(DataTransformer::class);
        $dataTransformer->transform(Argument::any())->willThrow($exception);

        $processor->addDataTransformer('context', $dataTransformer->reveal());

        $record = ['context' => []];
        $record = $processor($record);

        $this->assertArrayHasKey('extra', $record);
        $this->assertArrayHasKey('shadow-debug', $record['extra']);

        $this->assertArrayHasKey('property', $record['extra']['shadow-debug']);
        $this->assertSame('context', $record['extra']['shadow-debug']['property']);

        $this->assertArrayHasKey('field', $record['extra']['shadow-debug']);
        $this->assertSame('field', $record['extra']['shadow-debug']['field']);

        $this->assertArrayHasKey('message', $record['extra']['shadow-debug']);
        $this->assertSame('Message', $record['extra']['shadow-debug']['message']);
    }
}
