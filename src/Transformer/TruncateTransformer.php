<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Transformer;

use Aubes\ShadowLoggerBundle\Truncator\TruncatorInterface;

final class TruncateTransformer implements TransformerInterface
{
    public function __construct(private readonly TruncatorInterface $truncator)
    {
    }

    public function transform(mixed $data): string
    {
        if ($data === null) {
            return '';
        }

        if (!\is_scalar($data)) {
            throw new \InvalidArgumentException('Data must be scalar');
        }

        return $this->truncator->truncate((string) $data);
    }
}
