<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Encoder;

interface EncoderInterface
{
    public function hash(string $data): string;
}
