<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Visitor;

interface LoggerVisitorInterface
{
    public function has(array $record, string $field): bool;

    public function get(array $record, string $field): mixed;

    public function set(array &$record, string $field, mixed $value): void;
}
