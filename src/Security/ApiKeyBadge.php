<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

readonly class ApiKeyBadge implements BadgeInterface
{
    public function __construct(
        public readonly string $apiKey
    ) {
    }

    public function isResolved(): bool
    {
        return true;
    }
}
