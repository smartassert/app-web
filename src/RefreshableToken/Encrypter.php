<?php

declare(strict_types=1);

namespace App\RefreshableToken;

use SmartAssert\ApiClient\Data\User\Token;

readonly class Encrypter
{
    public function __construct(
        private \App\Security\Encrypter $encrypter,
        private Serializer $serializer,
    ) {
    }

    public function encrypt(Token $token): string
    {
        return $this->doEncrypt($this->serializer->serialize($token));
    }

    public function decrypt(string $ciphertext): ?Token
    {
        return $this->serializer->deserialize($this->doDecrypt($ciphertext));
    }

    private function doEncrypt(string $plaintext): string
    {
        return $this->encrypter->encrypt($plaintext);
    }

    private function doDecrypt(string $ciphertext): string
    {
        return $this->encrypter->decrypt($ciphertext) ?? '';
    }
}
