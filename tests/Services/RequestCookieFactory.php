<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Tests\Services\ApplicationClient\Client as ApplicationClient;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\BrowserKit\Cookie;

readonly class RequestCookieFactory
{
    public function create(ApplicationClient $client, string $sessionIdentifier): string
    {
        $response = $client->makeSignInPageWriteRequest('user@example.com', 'password');

        return sprintf(
            'id=%s; token=%s',
            $this->extractCookieValue($response, $sessionIdentifier),
            $this->extractCookieValue($response, 'token'),
        );
    }

    private function extractCookieValue(ResponseInterface $response, string $name): ?string
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
