<?php

declare(strict_types=1);

namespace App\Security;

use App\RefreshableToken\Encrypter;
use Symfony\Component\HttpFoundation\Request;

readonly class SymfonyRequestTokenExtractor
{
    public function __construct(
        private Encrypter $tokenEncrypter,
    ) {
    }

    /**
     * @return ?non-empty-string
     */
    public function extract(Request $request): ?string
    {
        $refreshableToken = $this->tokenEncrypter->decrypt(
            $request->cookies->getString('token')
        );

        return $refreshableToken?->token;
    }
}
