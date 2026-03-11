<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Transformer;

use Symfony\Component\HttpFoundation\IpUtils;

final class IpTransformer implements TransformerInterface
{
    /**
     * @SuppressWarnings(PMD.StaticAccess)
     */
    public function transform(mixed $data): string
    {
        if (empty($data) || !\is_string($data)) {
            throw new \InvalidArgumentException('Ip must be a string');
        }

        return IpUtils::anonymize($data);
    }
}
