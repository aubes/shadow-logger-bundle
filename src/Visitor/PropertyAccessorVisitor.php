<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Visitor;

use Aubes\ShadowLoggerBundle\Logger\TransformerException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class PropertyAccessorVisitor implements LoggerVisitorInterface
{
    public function __construct(private readonly PropertyAccessorInterface $accessor)
    {
    }

    public function has(array $record, string $field): bool
    {
        try {
            return $this->get($record, $field) !== null;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    public function get(array $record, string $field): mixed
    {
        return $this->accessor->getValue($record, $field);
    }

    /**
     * @psalm-suppress ReferenceConstraintViolation
     */
    public function set(array &$record, string $field, mixed $value): void
    {
        try {
            $this->accessor->setValue($record, $field, $value);
        } catch (\RuntimeException $e) {
            throw new TransformerException($field, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}
