<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Logger;

class TransformerException extends \RuntimeException
{
    protected string $field;

    /**
     * @param string    $message
     * @param int|mixed $code
     */
    public function __construct(string $field, $message = '', $code = 0, \Throwable $previous = null)
    {
        $this->field = $field;

        parent::__construct($message, $code, $previous);
    }

    public function getField(): string
    {
        return $this->field;
    }
}
