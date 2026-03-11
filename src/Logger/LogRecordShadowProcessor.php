<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Logger;

use Monolog\LogRecord;

/**
 * Logger processor for Monolog 3.
 */
final class LogRecordShadowProcessor
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

        if (isset($data['extra']) && !isset($this->mapping['extra'])) {
            $data['extra'] = \array_merge((array) $record['extra'], $data['extra']);
        }

        return $record->with(...$data);
    }
}
