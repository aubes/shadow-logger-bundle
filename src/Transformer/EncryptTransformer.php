<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Transformer;

use Aubes\ShadowLoggerBundle\Encryptor\EncryptorInterface;

final class EncryptTransformer implements TransformerInterface
{
    public function __construct(private readonly EncryptorInterface $encryptor)
    {
    }

    public function transform(mixed $data): array
    {
        if ($data === null) {
            return [];
        }

        if (!\is_scalar($data)) {
            throw new \InvalidArgumentException('Data must be scalar');
        }

        $iv = $this->encryptor->generateIv();

        return [
            'iv' => $iv,
            'value' => $this->encryptor->encrypt((string) $data, $iv),
        ];
    }
}
