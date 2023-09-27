<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\SymfonyTestClient\ClientInterface;
use Symfony\Component\Routing\RouterInterface;

readonly class Client
{
    public function __construct(
        private ClientInterface $client,
        private RouterInterface $router,
    ) {
    }

    public function makeSignInPageReadRequest(?string $userIdentifier, string $method = 'GET'): ResponseInterface
    {
        $queryParameters = [];
        if (null !== $userIdentifier) {
            $queryParameters['user-identifier'] = $userIdentifier;
        }

        return $this->client->makeRequest($method, $this->router->generate('sign_in_view', $queryParameters));
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
            $this->router->generate('sign_in_handle'),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($payload)
        );
    }

    public function makeDashboardReadRequest(?string $token, string $method = 'GET'): ResponseInterface
    {
        $headers = [];
        if (is_string($token)) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $this->client->makeRequest($method, $this->router->generate('dashboard'), $headers);
    }
}
