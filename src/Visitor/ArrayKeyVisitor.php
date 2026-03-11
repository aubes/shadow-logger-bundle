<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Visitor;

final class ArrayKeyVisitor implements LoggerVisitorInterface
{
    public function has(array $record, string $field): bool
    {
        return \array_key_exists($field, $record);
    }

    public function get(array $record, string $field): mixed
    {
        return $record[$field] ?? '';
    }

    public function set(array &$record, string $field, mixed $value): void
    {
        /** @psalm-suppress MixedAssignment */
        $record[$field] = $value;
    }
}
