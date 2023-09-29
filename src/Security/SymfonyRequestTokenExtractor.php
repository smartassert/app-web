<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;

class SymfonyRequestTokenExtractor
{
    /**
     * @return ?non-empty-string
     */
    public function extract(Request $request): ?string
    {
        $token = $request->cookies->getString('token');

        return '' === $token ? null : $token;
    }
}
