<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Transformer;

use Aubes\ShadowLoggerBundle\Encryptor\EncryptorInterface;

class EncryptTransformer implements TransformerInterface
{
    protected EncryptorInterface $encryptor;

    public function __construct(EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    public function transform($data): array
    {
        if (empty($data)) {
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
