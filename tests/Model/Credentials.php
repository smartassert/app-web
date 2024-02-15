<?php

declare(strict_types=1);

namespace App\Tests\Model;

readonly class Credentials
{
    public function __construct(
        private string $sessionIdentifier,
        private string $sessionId,
        private string $token,
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            '%s=%s; token=%s',
            $this->sessionIdentifier,
            $this->sessionId,
            $this->token,
        );
    }
}
