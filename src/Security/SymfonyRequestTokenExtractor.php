<?php

declare(strict_types=1);

namespace App\Security;

use App\RefreshableToken\Encrypter;
use SmartAssert\ApiClient\Model\RefreshableToken;
use Symfony\Component\HttpFoundation\Request;

readonly class SymfonyRequestTokenExtractor
{
    public function __construct(
        private Encrypter $tokenEncrypter,
    ) {
    }

    public function extract(Request $request): ?RefreshableToken
    {
        return $this->tokenEncrypter->decrypt($request->cookies->getString('token'));
    }
}
