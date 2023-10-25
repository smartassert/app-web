<?php

declare(strict_types=1);

namespace App\Tests\Application;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Model\RefreshableToken;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;
use Symfony\Component\HttpFoundation\Cookie;

abstract class AbstractLogoutTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider badMethodDataProvider
     */
    public function testReadBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeLogoutRequest(null, $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function badMethodDataProvider(): array
    {
        return [
            'DELETE' => [
                'method' => 'DELETE',
            ],
            'GET' => [
                'method' => 'GET',
            ],
            'PUT' => [
                'method' => 'PUT',
            ],
        ];
    }

    public function testLogoutSuccessWithAuthentication(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);

        $frontendToken = $frontendTokenProvider->get('user@example.com');

        $response = self::$staticApplicationClient->makeLogoutRequest(new RefreshableToken(
            $frontendToken->token,
            $frontendToken->refreshToken
        ));

        $this->assertLogoutSuccessResponse($response);
    }

    public function testLogoutSuccessWithoutAuthentication(): void
    {
        $response = self::$staticApplicationClient->makeLogoutRequest(null);

        $this->assertLogoutSuccessResponse($response);
    }

    private function assertLogoutSuccessResponse(ResponseInterface $response): void
    {
        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertStringContainsString('/sign-in/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());

        $responseCookieValue = $response->getHeaderLine('set-cookie');
        if ('' !== $responseCookieValue) {
            $responseCookie = Cookie::fromString($response->getHeaderLine('set-cookie'));

            self::assertSame('token', $responseCookie->getName());
            self::assertSame('deleted', $responseCookie->getValue());
        }
    }
}
