<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Transformer;

class StringTransformer implements TransformerInterface
{
    public function transform($data): string
    {
        if (empty($data)) {
            return '';
        }

        if (!\is_scalar($data) && !\method_exists($data, '__toString')) {
            throw new \InvalidArgumentException('Data is not stringable');
        }

        return (string) $data;
    }
}
