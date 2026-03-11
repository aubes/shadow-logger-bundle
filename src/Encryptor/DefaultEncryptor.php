<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Encryptor;

final class DefaultEncryptor implements EncryptorInterface
{
    public function __construct(
        private readonly string $key,
        private readonly string $cipher = 'aes-256-cbc',
    ) {
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
        $encrypted = \openssl_encrypt($data, $this->cipher, $this->key, 0, \base64_decode($iv));

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed.');
        }

        return $encrypted;
    }
}
