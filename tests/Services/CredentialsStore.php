<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Tests\Model\Credentials;
use App\Tests\Services\ApplicationClient\Client as ApplicationClient;
use Psr\Http\Message\ResponseInterface;

class CredentialsStore
{
    private Credentials $credentials;

    public function __construct(
        private readonly CookieExtractor $responseCookieExtractor,
    ) {
    }

    public function create(ApplicationClient $client, string $sessionIdentifier): void
    {
        $response = $client->makeSignInPageWriteRequest('user@example.com', 'password');

        $this->refresh($response, $sessionIdentifier);
    }

    public function get(): Credentials
    {
        return $this->credentials;
    }

    public function refresh(
        ResponseInterface $response,
        string $sessionIdentifier,
        ?string $sessionId = null,
    ): void {
        $responseSessionId = $this->responseCookieExtractor->extract($response, $sessionIdentifier);
        $requestSessionId = is_string($responseSessionId) ? $responseSessionId : (string) $sessionId;

        $this->credentials = new Credentials(
            $sessionIdentifier,
            $requestSessionId,
            (string) $this->responseCookieExtractor->extract($response, 'token')
        );
    }
}
