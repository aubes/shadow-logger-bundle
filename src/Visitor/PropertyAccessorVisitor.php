<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Visitor;

use Aubes\ShadowLoggerBundle\Logger\TransformerException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class PropertyAccessorVisitor implements LoggerVisitorInterface
{
    protected PropertyAccessorInterface $accessor;

    public function __construct(PropertyAccessorInterface $accessor)
    {
        $this->accessor = $accessor;
    }

    public function has(array $record, string $field): bool
    {
        try {
            return $this->get($record, $field) !== null;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function get(array $record, string $field)
    {
        return $this->accessor->getValue($record, $field);
    }

    /**
     * @psalm-suppress ReferenceConstraintViolation
     */
    public function set(array &$record, string $field, $value): void
    {
        try {
            $this->accessor->setValue($record, $field, $value);
        } catch (\RuntimeException $e) {
            throw new TransformerException($field, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}
