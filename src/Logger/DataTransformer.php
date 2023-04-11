<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Logger;

use Aubes\ShadowLoggerBundle\Visitor\LoggerVisitorInterface;

class DataTransformer
{
    protected string $field;
    protected LoggerVisitorInterface $visitor;
    protected array $transformers = [];
    protected bool $strict;

    public function __construct(string $field, LoggerVisitorInterface $visitor, array $transformers, bool $strict)
    {
        $this->field = $field;
        $this->visitor = $visitor;
        $this->transformers = $transformers;
        $this->strict = $strict;
    }

    public function transform(array &$record): void
    {
        if (!$this->visitor->has($record, $this->field)) {
            return;
        }

        $value = $this->visitor->get($record, $this->field);

        try {
            foreach ($this->transformers as $transformer) {
                $value = $transformer->transform($value);
            }
        } catch (\Exception $e) {
            if ($this->strict === true) {
                $value = null;
            }

            throw new TransformerException($this->field, $e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->visitor->set($record, $this->field, $value);
        }
    }
}
