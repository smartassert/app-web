<?php

declare(strict_types=1);

namespace App\RefreshableToken;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use SmartAssert\ApiClient\Model\RefreshableToken;

readonly class Encrypter
{
    /**
     * @param non-empty-string[] $keys
     */
    public function __construct(
        private array $keys,
        private Serializer $serializer,
    ) {
    }

    public function encrypt(RefreshableToken $token): string
    {
        return $this->doEncrypt($this->serializer->serialize($token));
    }

    public function decrypt(string $ciphertext): ?RefreshableToken
    {
        return $this->serializer->deserialize($this->doDecrypt($ciphertext));
    }

    private function doEncrypt(string $plaintext): string
    {
        foreach ($this->keys as $key) {
            try {
                return Crypto::encryptWithPassword($plaintext, $key);
            } catch (EnvironmentIsBrokenException) {
            }
        }

        return '';
    }

    private function doDecrypt(string $ciphertext): string
    {
        foreach ($this->keys as $key) {
            try {
                return Crypto::decryptWithPassword($ciphertext, $key);
            } catch (EnvironmentIsBrokenException | WrongKeyOrModifiedCiphertextException) {
            }
        }

        return '';
    }
}
