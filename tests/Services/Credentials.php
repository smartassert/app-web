<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Tests\Services\ApplicationClient\Client as ApplicationClient;
use Psr\Http\Message\ResponseInterface;

class Credentials implements \Stringable
{
    private string $sessionIdentifier = '';
    private string $sessionId = '';
    private string $token = '';

    public function __construct(
        private readonly CookieExtractor $responseCookieExtractor,
    ) {
    }

    public function __toString(): string
    {
        return sprintf('%s=%s; token=%s', $this->sessionIdentifier, $this->sessionId, $this->token);
    }

    public function create(ApplicationClient $client, string $sessionIdentifier): void
    {
        $response = $client->makeSignInPageWriteRequest('user@example.com', 'password');

        $this->refresh($response, $sessionIdentifier);
    }

    public function refresh(ResponseInterface $response, string $sessionIdentifier): void
    {
        $sessionId = $this->responseCookieExtractor->extract($response, $sessionIdentifier);
        if (is_string($sessionId)) {
            $this->sessionId = $sessionId;
        }

        $this->sessionIdentifier = $sessionIdentifier;
        $this->token = (string) $this->responseCookieExtractor->extract($response, 'token');
    }
}
