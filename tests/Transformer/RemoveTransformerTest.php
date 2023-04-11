<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Tests\Transformer;

use Aubes\ShadowLoggerBundle\Transformer\RemoveTransformer;
use PHPUnit\Framework\TestCase;

class RemoveTransformerTest extends TestCase
{
    public function testTransform()
    {
        $transformer = new RemoveTransformer();
        $this->assertSame('--obfuscated--', $transformer->transform('cogito ergo sum'));
        $this->assertSame('--obfuscated--', $transformer->transform(['cogito ergo sum']));
    }
}
