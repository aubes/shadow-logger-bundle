<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Visitor;

class ArrayKeyVisitor implements LoggerVisitorInterface
{
    public function has(array $record, string $field): bool
    {
        return \array_key_exists($field, $record);
    }

    /**
     * @return mixed
     */
    public function get(array $record, string $field)
    {
        return $record[$field] ?? '';
    }

    public function set(array &$record, string $field, $value): void
    {
        $record[$field] = $value;
    }
}
