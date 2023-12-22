<?php

declare(strict_types=1);

namespace App\Security;

use App\RefreshableToken\Encrypter;
use Psr\Http\Message\ServerRequestInterface;
use SmartAssert\ApiClient\Data\User\Token;

readonly class RequestTokenExtractor
{
    public function __construct(
        private Encrypter $tokenEncrypter,
    ) {
    }

    public function extract(ServerRequestInterface $request): ?Token
    {
        $cookies = $request->getCookieParams();
        $tokenCookie = $cookies['token'] ?? '';

        return $this->tokenEncrypter->decrypt($tokenCookie);
    }
}
