<?php

declare(strict_types=1);

namespace App\Security;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;

readonly class Encrypter
{
    /**
     * @param non-empty-string[] $keys
     */
    public function __construct(
        private array $keys,
    ) {
    }

    public function encrypt(string $data): string
    {
        return $this->doEncrypt($data);
    }

    public function decrypt(string $ciphertext): ?string
    {
        return $this->doDecrypt($ciphertext);
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
