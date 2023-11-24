<?php

declare(strict_types=1);

namespace App\Security;

readonly class ApiKey
{
    /**
     * @param non-empty-string $key
     */
    public function __construct(
        public readonly string $key
    ) {
    }
}
