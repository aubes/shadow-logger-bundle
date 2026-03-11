<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Truncator;

interface TruncatorInterface
{
    public function truncate(string $data): string;
}
