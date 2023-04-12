<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\Encryptor;

interface EncryptorInterface
{
    public function encrypt(string $data, string $iv): string;

    public function generateIv(): string;
}
