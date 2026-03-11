<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Transformer;

interface TransformerInterface
{
    /**
     * @return array|scalar
     */
    public function transform(mixed $data): mixed;
}
