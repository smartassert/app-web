<?php

declare(strict_types=1);

namespace App\Tests\Services;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\BrowserKit\Cookie;

readonly class CookieExtractor
{
    public function extract(ResponseInterface $response, string $name): ?string
    {
        foreach ($response->getHeader('set-cookie') as $setCookieLine) {
            if (str_starts_with($setCookieLine, $name . '=')) {
                $sessionIdCookie = Cookie::fromString($setCookieLine);

                return $sessionIdCookie->getValue();
            }
        }

        return null;
    }
}
