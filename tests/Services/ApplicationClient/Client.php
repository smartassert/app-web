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

    public function makeDashboardReadRequest(?string $credentials = null): ResponseInterface
    {
        $credentials = null === $credentials ? $this->getCredentials() : $credentials;
        $response = $this->client->makeRequest('GET', '/', ['cookie' => $credentials]);

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    public function makeLogoutRequest(string $method = 'POST', ?string $credentials = null): ResponseInterface
    {
        $credentials = null === $credentials ? $this->getCredentials() : $credentials;

        $response = $this->client->makeRequest($method, '/logout/', ['cookie' => $credentials]);

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    public function makeSourcesReadRequest(?string $credentials = null): ResponseInterface
    {
        $credentials = null === $credentials ? $this->getCredentials() : $credentials;

        $response = $this->client->makeRequest(
            'GET',
            '/sources',
            ['cookie' => $credentials]
        );

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    public function makeFileSourceAddRequest(string $label, ?string $credentials = null): ResponseInterface
    {
        $credentials = null === $credentials ? $this->getCredentials() : $credentials;

        $response = $this->client->makeRequest(
            'POST',
            '/sources/file',
            [
                'cookie' => $credentials,
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query(['label' => $label])
        );

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    public function makeFileSourceReadRequest(string $id, ?string $credentials = null): ResponseInterface
    {
        $credentials = null === $credentials ? $this->getCredentials() : $credentials;

        $response = $this->client->makeRequest(
            'GET',
            '/sources/file/' . $id,
            ['cookie' => $credentials]
        );

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    public function makeFileSourceFileCreateRequest(string $id, string $filename, string $content): ResponseInterface
    {
        $response = $this->client->makeRequest(
            'POST',
            '/sources/file/' . $id,
            [
                'cookie' => $this->getCredentials(),
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query(['filename' => $filename, 'content' => $content])
        );

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    public function makeFileSourceFileUpdateRequest(string $id, string $filename, string $content): ResponseInterface
    {
        $response = $this->client->makeRequest(
            'POST',
            '/sources/file/' . $id . '/' . $filename,
            [
                'cookie' => $this->getCredentials(),
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query(['content' => $content])
        );

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    public function getCredentials(): string
    {
        if (!isset($this->sessionId) && !isset($this->token)) {
            $response = $this->makeSignInPageWriteRequest(
                'user@example.com',
                'password'
            );

            $this->extractCredentialsFromResponse($response);
        }

        return sprintf('%s=%s; token=%s', $this->sessionIdentifier, $this->sessionId, $this->token);
    }

    public function makeFileSourceFileViewRequest(string $fileSourceId, string $filename): ResponseInterface
    {
        return $this->client->makeRequest(
            'GET',
            '/sources/file/' . $fileSourceId . '/' . $filename,
            [
                'cookie' => $this->getCredentials(),
            ]
        );
    }

    public function makeSuitesReadRequest(?string $credentials = null): ResponseInterface
    {
        $credentials = null === $credentials ? $this->getCredentials() : $credentials;

        $response = $this->client->makeRequest(
            'GET',
            '/suites',
            ['cookie' => $credentials]
        );

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    /**
     * @param string[] $tests
     */
    public function makeCreateSuiteRequest(string $sourceId, string $label, array $tests): ResponseInterface
    {
        $response = $this->client->makeRequest(
            'POST',
            '/suites',
            [
                'cookie' => $this->getCredentials(),
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query(['source_id' => $sourceId, 'label' => $label, 'tests' => implode("\n", $tests)])
        );

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    public function makeViewSuiteRequest(string $suiteId): ResponseInterface
    {
        $response = $this->client->makeRequest(
            'GET',
            '/suite/' . $suiteId,
            [
                'cookie' => $this->getCredentials(),
            ]
        );

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    public function makeCreateJobRequest(string $suiteId): ResponseInterface
    {
        $response = $this->client->makeRequest(
            'POST',
            '/job/' . $suiteId,
            [
                'cookie' => $this->getCredentials(),
            ]
        );

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    public function makeViewJobRequest(string $jobId): ResponseInterface
    {
        $response = $this->client->makeRequest(
            'GET',
            '/job/' . $jobId,
            [
                'cookie' => $this->getCredentials(),
            ]
        );

        $this->extractCredentialsFromResponse($response);

        return $response;
    }

    private function extractCredentialsFromResponse(ResponseInterface $response): void
    {
        $sessionId = $this->responseCookieExtractor->extract($response, $this->sessionIdentifier);
        if (is_string($sessionId)) {
            $this->sessionId = $sessionId;
        }

        $this->token = (string) $this->responseCookieExtractor->extract($response, 'token');
    }
}
