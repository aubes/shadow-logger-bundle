<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Encryptor;

final class DefaultEncryptor implements EncryptorInterface
{
    public function __construct(
        private readonly string $key,
        private readonly string $cipher = 'aes-256-cbc',
    ) {
        if (\strlen($key) < 16) {
            throw new \InvalidArgumentException('Encryption key must be at least 16 bytes.');
        }

        if (!\in_array($cipher, \openssl_get_cipher_methods(), true)) {
            throw new \InvalidArgumentException(\sprintf('Invalid cipher "%s".', $cipher));
        }
    }

    public function generateIv(): string
    {
        $length = \openssl_cipher_iv_length($this->cipher);

        if ($length === false) {
            throw new \RuntimeException(\sprintf('Could not determine IV length for cipher "%s".', $this->cipher));
        }

        return \base64_encode(\random_bytes($length));
    }

    public function encrypt(string $data, string $iv): string
    {
        $decodedIv = \base64_decode($iv, true);

        if ($decodedIv === false) {
            throw new \InvalidArgumentException('Invalid IV: base64 decoding failed.');
        }

        $encrypted = \openssl_encrypt($data, $this->cipher, $this->key, 0, $decodedIv);

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed.');
        }

        return $encrypted;
    }
}
