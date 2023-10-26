<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\RefreshableToken\Encrypter;
use SmartAssert\ApiClient\Model\RefreshableToken;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;

abstract class AbstractSignInReadTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testReadBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeSignInPageReadRequest(
            userIdentifier: null,
            method: $method,
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createBadMethodDataProvider(): array
    {
        return [
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'DELETE',
            ],
        ];
    }

    public function testReadSuccess(): void
    {
        $response = self::$staticApplicationClient->makeSignInPageReadRequest(null);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }

    public function testReadWhenSignedInRedirectsToDashboard(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);

        $frontendToken = $frontendTokenProvider->get('user@example.com');
        $refreshableToken = new RefreshableToken($frontendToken->token, $frontendToken->refreshToken);

        $tokenEncrypter = self::getContainer()->get(Encrypter::class);
        \assert($tokenEncrypter instanceof Encrypter);

        $response = self::$staticApplicationClient->makeSignInPageReadRequest(
            userIdentifier: null,
            token: $refreshableToken,
        );

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertSame('/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());
    }
}
