<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Logger;

class ShadowProcessor
{
    protected bool $debug;

    /** @var array<array-key, array<DataTransformer>> */
    protected array $mapping = [];

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function addDataTransformer(string $property, DataTransformer $dataTransformer): void
    {
        if (!isset($this->mapping[$property])) {
            $this->mapping[$property] = [];
        }

        $this->mapping[$property][] = $dataTransformer;
    }

    public function __invoke(array $record): array
    {
        foreach ($this->mapping as $property => $dataTransformers) {
            foreach ($dataTransformers as $dataTransformer) {
                try {
                    $dataTransformer->transform($record[$property]);
                } catch (TransformerException $e) {
                    if ($this->debug) {
                        $debug = [
                            'property' => $property,
                            'field' => $e->getField(),
                            'message' => $e->getMessage(),
                        ];

                        $record['extra']['shadow-debug'] = $debug;
                    }
                }
            }
        }

        return $record;
    }
}
