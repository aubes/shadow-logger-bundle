<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Transformer;

class RemoveTransformer implements TransformerInterface
{
    public function transform($data): string
    {
        return '--obfuscated--';
    }
}
