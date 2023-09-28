<?php

declare(strict_types=1);

namespace App\Security;

readonly class UserCredentials
{
    /**
     * @param ?non-empty-string $userIdentifier
     * @param ?non-empty-string $password
     */
    public function __construct(
        public ?string $userIdentifier,
        public ?string $password,
    ) {
    }
}
