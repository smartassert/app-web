<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use App\Enum\Routes;
use App\RefreshableToken\Encrypter;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Model\RefreshableToken;
use SmartAssert\SymfonyTestClient\ClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class Client
{
    public function __construct(
        private ClientInterface $client,
        private UrlGeneratorInterface $urlGenerator,
        private Encrypter $tokenEncrypter,
    ) {
    }

    public function makeSignInPageReadRequest(
        ?string $userIdentifier,
        ?RefreshableToken $token = null,
        string $method = 'GET'
    ): ResponseInterface {
        $queryParameters = [];
        if (null !== $userIdentifier) {
            $queryParameters['user-identifier'] = $userIdentifier;
        }

        $headers = [];
        if ($token instanceof RefreshableToken) {
            $encryptedToken = $this->tokenEncrypter->encrypt($token);

            $headers['cookie'] = 'token=' . $encryptedToken;
        }

        return $this->client->makeRequest(
            $method,
            $this->urlGenerator->generate(Routes::SIGN_IN_VIEW_NAME->value, $queryParameters),
            $headers
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

    public function makeDashboardReadRequest(?RefreshableToken $token, string $method = 'GET'): ResponseInterface
    {
        $headers = [];
        if ($token instanceof RefreshableToken) {
            $encryptedToken = $this->tokenEncrypter->encrypt($token);

            $headers['cookie'] = 'token=' . $encryptedToken;
        }

        return $this->client->makeRequest(
            $method,
            $this->urlGenerator->generate(Routes::DASHBOARD_NAME->value),
            $headers
        );
    }

    public function makeLogoutRequest(?RefreshableToken $token, string $method = 'POST'): ResponseInterface
    {
        $headers = [];
        if ($token instanceof RefreshableToken) {
            $encryptedToken = $this->tokenEncrypter->encrypt($token);

            $headers['cookie'] = 'token=' . $encryptedToken;
        }

        return $this->client->makeRequest($method, $this->urlGenerator->generate('log_out_handle'), $headers);
    }
}
