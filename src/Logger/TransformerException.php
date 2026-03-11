<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Logger;

final class TransformerException extends \RuntimeException
{
    public function __construct(private readonly string $field, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getField(): string
    {
        return $this->field;
    }
}
