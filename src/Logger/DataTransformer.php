<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Logger;

use Aubes\ShadowLoggerBundle\Visitor\LoggerVisitorInterface;

class DataTransformer
{
    public function __construct(
        private readonly string $field,
        private readonly LoggerVisitorInterface $visitor,
        private readonly array $transformers,
        private readonly bool $strict,
    ) {
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
