<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Tests\Services\ApplicationClient\Client as ApplicationClient;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\BrowserKit\Cookie;

readonly class RequestCookieFactory
{
    public function createFromResponse(ResponseInterface $response, string $sessionIdentifier): string
    {
        $setCookieLines = $response->getHeader('set-cookie');

        $sessionId = null;
        $authenticationToken = null;

        foreach ($setCookieLines as $setCookieLine) {
            if (str_starts_with($setCookieLine, 'token=')) {
                $tokenCookie = \Symfony\Component\HttpFoundation\Cookie::fromString($setCookieLine);
                $authenticationToken = $tokenCookie->getValue();
            }

            if (str_starts_with($setCookieLine, $sessionIdentifier . '=')) {
                $sessionIdCookie = Cookie::fromString($setCookieLine);
                $sessionId = $sessionIdCookie->getValue();
            }
        }

        return sprintf(
            'id=%s; token=%s',
            $sessionId,
            $authenticationToken,
        );
    }

    public function create(ApplicationClient $client, string $sessionIdentifier): string
    {
        $response = $client->makeSignInPageWriteRequest('user@example.com', 'password');

        $setCookieLines = $response->getHeader('set-cookie');

        $sessionId = null;
        $authenticationToken = null;

        foreach ($setCookieLines as $setCookieLine) {
            if (str_starts_with($setCookieLine, 'token=')) {
                $tokenCookie = \Symfony\Component\HttpFoundation\Cookie::fromString($setCookieLine);
                $authenticationToken = $tokenCookie->getValue();
            }

            if (str_starts_with($setCookieLine, $sessionIdentifier . '=')) {
                $sessionIdCookie = Cookie::fromString($setCookieLine);
                $sessionId = $sessionIdCookie->getValue();
            }
        }

        return sprintf(
            'id=%s; token=%s',
            $sessionId,
            $authenticationToken,
        );
    }
}
