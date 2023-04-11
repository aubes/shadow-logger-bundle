<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Encoder;

use Aubes\ShadowLoggerBundle\Encoder\Encoder;
use PHPUnit\Framework\TestCase;

class EncoderTest extends TestCase
{
    public function testHashSuccess()
    {
        $encoder = new Encoder();

        $this->assertSame('3a6eb0790f39ac87c94f3856b2dd2c5d110e6811602261a9a923d3bb23adc8b7', $encoder->hash('data'));
    }

    public function testSaltedHashSuccess()
    {
        $encoder = new Encoder('md5', 'md5-bad-idea');

        $this->assertSame('3af22f60687bc2f1b6f1e4c73a2638ed', $encoder->hash('data'));
    }

    public function testInvalidAlgo()
    {
        $this->expectException(\InvalidArgumentException::class);

        $encoder = new Encoder('WAT');
    }
}
