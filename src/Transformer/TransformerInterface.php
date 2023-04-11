<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Transformer;

interface TransformerInterface
{
    /**
     * @param mixed $data
     *
     * @return array|scalar
     */
    public function transform($data);
}
