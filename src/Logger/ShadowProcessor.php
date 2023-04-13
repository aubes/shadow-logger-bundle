<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Logger;

/**
 * Logger processor for Monolog 2.
 */
class ShadowProcessor
{
    use ShadowProcessorTrait;

    public function __invoke(array $record): array
    {
        foreach ($this->mapping as $property => $dataTransformers) {
            $this->applyTransformers($dataTransformers, $record, $property);
        }

        return $record;
    }
}
