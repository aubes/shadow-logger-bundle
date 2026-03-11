<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Encoder;

final class Encoder implements EncoderInterface
{
    public function __construct(
        private readonly string $algo = 'sha256',
        private readonly string $salt = '',
        private readonly bool $binary = false,
    ) {
        if (!\in_array($algo, \hash_algos())) {
            throw new \InvalidArgumentException('Invalid algo');
        }
    }

    public function hash(string $data): string
    {
        return \hash($this->algo, $this->salt . $data, $this->binary);
    }
}
