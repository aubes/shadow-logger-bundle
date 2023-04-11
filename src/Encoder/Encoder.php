<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Encoder;

class Encoder implements EncoderInterface
{
    protected string $algo;
    protected string $salt;
    protected bool $binary;

    public function __construct(string $algo = 'sha256', string $salt = '', bool $binary = false)
    {
        if (!\in_array($algo, \hash_algos())) {
            throw new \InvalidArgumentException('Invalid algo');
        }

        $this->algo = $algo;
        $this->salt = $salt;
        $this->binary = $binary;
    }

    public function hash(string $data): string
    {
        return \hash($this->algo, $this->salt . $data, $this->binary);
    }
}
