<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use App\Enum\Routes;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\SymfonyTestClient\ClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class Client
{
    public function __construct(
        private ClientInterface $client,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function makeSignInPageReadRequest(
        ?string $userIdentifier = null,
        string $cookie = '',
        string $method = 'GET'
    ): ResponseInterface {
        $queryParameters = [];
        if (null !== $userIdentifier) {
            $queryParameters['user-identifier'] = $userIdentifier;
        }

        return $this->client->makeRequest(
            $method,
            $this->urlGenerator->generate(Routes::SIGN_IN_VIEW_NAME->value, $queryParameters),
            ['cookie' => $cookie]
        );
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
            $this->urlGenerator->generate('sign_in_handle'),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($payload)
        );
    }

    public function makeDashboardReadRequest(string $cookie): ResponseInterface
    {
        return $this->client->makeRequest(
            'GET',
            $this->urlGenerator->generate(Routes::DASHBOARD_NAME->value),
            ['cookie' => $cookie]
        );
    }

    public function makeLogoutRequest(string $cookie = '', string $method = 'POST'): ResponseInterface
    {
        return $this->client->makeRequest(
            $method,
            $this->urlGenerator->generate('log_out_handle'),
            ['cookie' => $cookie]
        );
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
