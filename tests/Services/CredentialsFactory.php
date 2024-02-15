<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Tests\Model\Credentials;
use App\Tests\Services\ApplicationClient\Client as ApplicationClient;
use Psr\Http\Message\ResponseInterface;

readonly class CredentialsFactory
{
    public function __construct(
        private CookieExtractor $responseCookieExtractor,
    ) {
    }

    public function create(ApplicationClient $client, string $sessionIdentifier): Credentials
    {
        $response = $client->makeSignInPageWriteRequest('user@example.com', 'password');

        return $this->createFromResponse($response, $sessionIdentifier);
    }

    public function createFromResponse(
        ResponseInterface $response,
        string $sessionIdentifier,
        ?string $sessionId = null,
    ): Credentials {
        $responseSessionId = $this->responseCookieExtractor->extract($response, $sessionIdentifier);
        $requestSessionId = is_string($responseSessionId) ? $responseSessionId : (string) $sessionId;

        return new Credentials(
            $sessionIdentifier,
            $requestSessionId,
            (string) $this->responseCookieExtractor->extract($response, 'token')
        );
    }
}
