<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Truncator;

final class Truncator implements TruncatorInterface
{
    public function __construct(
        private readonly int $keepStart = 2,
        private readonly int $keepEnd = 2,
        private readonly string $mask = '***',
    ) {
    }

    public function truncate(string $data): string
    {
        $length = \mb_strlen($data);

        if ($length <= $this->keepStart + $this->keepEnd) {
            return $this->mask;
        }

        $start = $this->keepStart > 0 ? \mb_substr($data, 0, $this->keepStart) : '';
        $end = $this->keepEnd > 0 ? \mb_substr($data, -$this->keepEnd) : '';

        return $start . $this->mask . $end;
    }
}
