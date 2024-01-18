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
        string $cookie = '',
        string $method = 'GET'
    ): ResponseInterface {
        $url = '/sign-in/';
        if (null !== $userIdentifier) {
            $url .= '?user-identifier=' . $userIdentifier;
        }

        return $this->client->makeRequest($method, $url, ['cookie' => $cookie]);
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

    public function makeDashboardReadRequest(string $cookie): ResponseInterface
    {
        return $this->client->makeRequest('GET', '/', ['cookie' => $cookie]);
    }

    public function makeLogoutRequest(string $cookie = '', string $method = 'POST'): ResponseInterface
    {
        return $this->client->makeRequest($method, '/logout/', ['cookie' => $cookie]);
    }

    public function makeSourcesReadRequest(string $cookie): ResponseInterface
    {
        return $this->client->makeRequest(
            'GET',
            $this->urlGenerator->generate(Routes::SOURCES_NAME->value),
            ['cookie' => $cookie]
        );
    }
}
