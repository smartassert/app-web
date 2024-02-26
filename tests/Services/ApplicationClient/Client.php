<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use App\Tests\Services\CookieExtractor;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\SymfonyTestClient\ClientInterface;

class Client
{
    private string $sessionId;
    private string $token;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $sessionIdentifier,
        private readonly CookieExtractor $responseCookieExtractor,
    ) {
    }

    public function makeSignInPageReadRequest(
        string $userIdentifier = null,
        string $credentials = null,
        string $method = 'GET'
    ): ResponseInterface {
        $url = '/sign-in/';
        if (null !== $userIdentifier) {
            $url .= '?user-identifier=' . $userIdentifier;
        }

        return $this->client->makeRequest($method, $url, ['cookie' => (string) $credentials]);
    }

    public function makeSignInPageWriteRequest(
        ?string $userIdentifier,
        ?string $password,
        string $method = 'POST'
    ): ResponseInterface {
        $payload = [];

        if (is_string($userIdentifier)) {
            $payload['user-identifier'] = $userIdentifier;
        }

        if (is_string($password)) {
            $payload['password'] = $password;
        }

        return $this->client->makeRequest(
            $method,
            '/sign-in/',
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($payload)
        );
    }

    public function makeDashboardReadRequest(?string $credentials = null): ResponseInterface
    {
        $credentials = null === $credentials ? $this->foo() : $credentials;
        $response = $this->client->makeRequest('GET', '/', ['cookie' => $credentials]);

        $this->handleFooResponse($response);

        return $response;
    }

    public function makeLogoutRequest(string $method = 'POST', ?string $credentials = null): ResponseInterface
    {
        $credentials = null === $credentials ? $this->foo() : $credentials;

        $response = $this->client->makeRequest($method, '/logout/', ['cookie' => $credentials]);

        $this->handleFooResponse($response);

        return $response;
    }

    public function makeSourcesReadRequest(?string $credentials = null): ResponseInterface
    {
        $credentials = null === $credentials ? $this->foo() : $credentials;

        $response = $this->client->makeRequest(
            'GET',
            '/sources',
            ['cookie' => $credentials]
        );

        $this->handleFooResponse($response);

        return $response;
    }

    public function makeFileSourceAddRequest(string $label, ?string $credentials = null): ResponseInterface
    {
        $credentials = null === $credentials ? $this->foo() : $credentials;

        $response = $this->client->makeRequest(
            'POST',
            '/sources/file',
            [
                'cookie' => $credentials,
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query(['label' => $label])
        );

        $this->handleFooResponse($response);

        return $response;
    }

    public function makeFileSourceReadRequest(string $id, ?string $credentials = null): ResponseInterface
    {
        $credentials = null === $credentials ? $this->foo() : $credentials;

        $response = $this->client->makeRequest(
            'GET',
            '/sources/file/' . $id,
            ['cookie' => $credentials]
        );

        $this->handleFooResponse($response);

        return $response;
    }

    public function makeFileSourceFileCreateRequest(string $id, string $filename, string $content): ResponseInterface
    {
        $response = $this->client->makeRequest(
            'POST',
            '/sources/file/' . $id,
            [
                'cookie' => $this->foo(),
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query(['filename' => $filename, 'content' => $content])
        );

        $this->handleFooResponse($response);

        return $response;
    }

    public function foo(): string
    {
        if (!isset($this->sessionId) && !isset($this->token)) {
            $this->createFoo();
        }

        return sprintf('%s=%s; token=%s', $this->sessionIdentifier, $this->sessionId, $this->token);
    }

    private function createFoo(): void
    {
        $response = $this->makeSignInPageWriteRequest(
            'user@example.com',
            'password'
        );

        $this->handleFooResponse($response);
    }

    private function handleFooResponse(ResponseInterface $response): void
    {
        $sessionId = $this->responseCookieExtractor->extract($response, $this->sessionIdentifier);
        if (is_string($sessionId)) {
            $this->sessionId = $sessionId;
        }

        $this->token = (string) $this->responseCookieExtractor->extract($response, 'token');
    }
}
