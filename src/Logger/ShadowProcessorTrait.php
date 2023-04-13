<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Logger;

trait ShadowProcessorTrait
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

    protected function applyTransformers(array $dataTransformers, array &$data, string $property): void
    {
        foreach ($dataTransformers as $dataTransformer) {
            try {
                $dataTransformer->transform($data[$property]);
            } catch (TransformerException $e) {
                if ($this->debug) {
                    $debug = [
                        'property' => $property,
                        'field' => $e->getField(),
                        'message' => $e->getMessage(),
                    ];

                    $data['extra']['shadow-debug'] = $debug;
                }
            }
        }
    }
}
