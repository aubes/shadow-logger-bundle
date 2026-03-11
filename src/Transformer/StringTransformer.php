<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Transformer;

final class StringTransformer implements TransformerInterface
{
    public function transform(mixed $data): string
    {
        if ($data === null) {
            return '';
        }

        if (\is_scalar($data) || (\is_object($data) && \method_exists($data, '__toString'))) {
            return (string) $data;
        }

        throw new \InvalidArgumentException('Data is not stringable');
    }
}
