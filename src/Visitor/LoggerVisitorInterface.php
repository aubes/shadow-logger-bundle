<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Visitor;

interface LoggerVisitorInterface
{
    public function has(array $record, string $field): bool;

    /**
     * @return mixed
     */
    public function get(array $record, string $field);

    /**
     * @param mixed $value
     */
    public function set(array &$record, string $field, $value): void;
}
