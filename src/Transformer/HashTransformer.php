<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Transformer;

use Aubes\ShadowLoggerBundle\Encoder\EncoderInterface;

class HashTransformer implements TransformerInterface
{
    protected EncoderInterface $encoder;

    public function __construct(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function transform($data): string
    {
        if (empty($data)) {
            return '';
        }

        if (!\is_scalar($data)) {
            throw new \InvalidArgumentException('Data must be scalar');
        }

        return $this->encoder->hash((string) $data);
    }
}
