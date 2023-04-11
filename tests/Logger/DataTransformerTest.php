<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Logger;

use Aubes\ShadowLoggerBundle\Logger\DataTransformer;
use Aubes\ShadowLoggerBundle\Logger\TransformerException;
use Aubes\ShadowLoggerBundle\Transformer\TransformerInterface;
use Aubes\ShadowLoggerBundle\Visitor\LoggerVisitorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class DataTransformerTest extends TestCase
{
    use ProphecyTrait;

    public function testTransform()
    {
        $visitor = $this->prophesize(LoggerVisitorInterface::class);
        $visitor->has(Argument::any(), Argument::exact('field'))->shouldBeCalledOnce()->willReturn(true);
        $visitor->get(Argument::any(), Argument::exact('field'))->shouldBeCalledOnce()->willReturn('data');
        $visitor->set(Argument::any(), Argument::exact('field'), Argument::exact('data-change'))->shouldBeCalledOnce();

        $transformer = $this->prophesize(TransformerInterface::class);
        $transformer->transform(Argument::exact('data'))->willReturn('data-change');

        $record = [];

        $dataTransformer = new DataTransformer('field', $visitor->reveal(), [$transformer->reveal()], true);
        $dataTransformer->transform($record);
    }

    public function testTransformFieldNotExist()
    {
        $visitor = $this->prophesize(LoggerVisitorInterface::class);
        $visitor->has(Argument::any(), Argument::any())->shouldBeCalledOnce()->willReturn(false);
        $visitor->get(Argument::any(), Argument::any())->shouldNotBeCalled();
        $visitor->set(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

        $transformer = $this->prophesize(TransformerInterface::class);
        $transformer->transform(Argument::exact('data'))->willReturn('data-change');

        $record = [];

        $dataTransformer = new DataTransformer('field', $visitor->reveal(), [$transformer->reveal()], true);
        $dataTransformer->transform($record);
    }

    public function testTransformExceptionNotStrict()
    {
        $visitor = $this->prophesize(LoggerVisitorInterface::class);
        $visitor->has(Argument::any(), Argument::exact('field'))->shouldBeCalledOnce()->willReturn(true);
        $visitor->get(Argument::any(), Argument::exact('field'))->shouldBeCalledOnce()->willReturn('data');
        $visitor->set(Argument::any(), Argument::exact('field'), Argument::exact('data'))->shouldBeCalledOnce();

        $transformer = $this->prophesize(TransformerInterface::class);
        $transformer->transform(Argument::exact('data'))->willThrow(\Exception::class);

        $record = [];

        $dataTransformer = new DataTransformer('field', $visitor->reveal(), [$transformer->reveal()], false);

        $this->expectException(TransformerException::class);
        $dataTransformer->transform($record);
    }

    public function testTransformExceptionStrict()
    {
        $visitor = $this->prophesize(LoggerVisitorInterface::class);
        $visitor->has(Argument::any(), Argument::exact('field'))->shouldBeCalledOnce()->willReturn(true);
        $visitor->get(Argument::any(), Argument::exact('field'))->shouldBeCalledOnce()->willReturn('data');
        $visitor->set(Argument::any(), Argument::exact('field'), Argument::exact(null))->shouldBeCalledOnce();

        $transformer = $this->prophesize(TransformerInterface::class);
        $transformer->transform(Argument::exact('data'))->willThrow(\Exception::class);

        $record = [];

        $dataTransformer = new DataTransformer('field', $visitor->reveal(), [$transformer->reveal()], true);

        $this->expectException(TransformerException::class);
        $dataTransformer->transform($record);
    }
}
