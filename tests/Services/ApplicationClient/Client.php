<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\SymfonyTestClient\ClientInterface;

readonly class Client
{
    public function __construct(
        private ClientInterface $client,
    ) {
    }

    public function makeSignInPageReadRequest(
        ?string $userIdentifier = null,
        ?string $credentials = null,
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

    public function makeDashboardReadRequest(string $credentials): ResponseInterface
    {
        return $this->client->makeRequest('GET', '/', ['cookie' => $credentials]);
    }

    public function makeLogoutRequest(?string $credentials = null, string $method = 'POST'): ResponseInterface
    {
        return $this->client->makeRequest($method, '/logout/', ['cookie' => (string) $credentials]);
    }

    public function makeSourcesReadRequest(string $credentials): ResponseInterface
    {
        return $this->client->makeRequest(
            'GET',
            '/sources',
            ['cookie' => $credentials]
        );
    }

    public function makeFileSourceAddRequest(string $credentials, string $label): ResponseInterface
    {
        return $this->client->makeRequest(
            'POST',
            '/sources/file',
            [
                'cookie' => $credentials,
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query(['label' => $label])
        );
    }

    public function makeFileSourceReadRequest(string $credentials, string $id): ResponseInterface
    {
        return $this->client->makeRequest(
            'GET',
            '/sources/file/' . $id,
            ['cookie' => $credentials]
        );
    }

    public function makeFileSourceFileCreateRequest(
        string $credentials,
        string $id,
        string $filename,
        string $content
    ): ResponseInterface {
        return $this->client->makeRequest(
            'POST',
            '/sources/file/' . $id,
            [
                'cookie' => $credentials,
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query(['filename' => $filename, 'content' => $content])
        );
    }
}
