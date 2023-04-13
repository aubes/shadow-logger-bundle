<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Logger;

use Monolog\LogRecord;

/**
 * Logger processor for Monolog 3.
 */
class LogRecordShadowProcessor
{
    use ShadowProcessorTrait;

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $data = [];

        foreach ($this->mapping as $property => $dataTransformers) {
            $data[$property] = $record[$property];

            $this->applyTransformers($dataTransformers, $data, $property);
        }

        return $record->with(...$data);
    }
}
