<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Transformer;

final class RemoveTransformer implements TransformerInterface
{
    public function transform(mixed $data): string
    {
        return '--obfuscated--';
    }
}
