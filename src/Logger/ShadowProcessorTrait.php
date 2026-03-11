<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Logger;

trait ShadowProcessorTrait
{
    /** @var array<string, list<DataTransformer>> */
    protected array $mapping = [];

    public function __construct(protected readonly bool $debug)
    {
    }

    public function addDataTransformer(string $property, DataTransformer $dataTransformer): void
    {
        if (!isset($this->mapping[$property])) {
            $this->mapping[$property] = [];
        }

        $this->mapping[$property][] = $dataTransformer;
    }

    /** @param list<DataTransformer> $dataTransformers */
    protected function applyTransformers(array $dataTransformers, array &$data, string $property): void
    {
        foreach ($dataTransformers as $dataTransformer) {
            try {
                /** @psalm-suppress MixedArgument */
                $dataTransformer->transform($data[$property]);
            } catch (TransformerException $e) {
                if ($this->debug) {
                    $debug = [
                        'property' => $property,
                        'field' => $e->getField(),
                        'message' => $e->getMessage(),
                    ];

                    /** @psalm-suppress MixedArrayAssignment */
                    $data['extra']['shadow-debug'] = $debug;
                }
            }
        }
    }
}
