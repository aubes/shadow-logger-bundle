<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Logger;

final class TransformerException extends \RuntimeException
{
    /**
     * @param string    $message
     * @param int|mixed $code
     */
    public function __construct(private readonly string $field, $message = '', $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getField(): string
    {
        return $this->field;
    }
}
