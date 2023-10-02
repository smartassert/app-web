<?php

declare(strict_types=1);

namespace App\Tests\Application;

use Symfony\Component\HttpFoundation\Cookie;

abstract class AbstractSignInWriteTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testWriteBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeSignInPageWriteRequest(null, null, $method);

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

    public function testWriteUnauthorized(): void
    {
        $response = self::$staticApplicationClient->makeSignInPageWriteRequest('user@example.com', 'invalid');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertStringContainsString('/sign-in/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());

        $responseCookieValue = $response->getHeaderLine('set-cookie');
        if ('' !== $responseCookieValue) {
            $responseCookie = Cookie::fromString($response->getHeaderLine('set-cookie'));
            self::assertNotSame('token', $responseCookie->getName());
        }
    }

    public function testWriteSuccess(): void
    {
        $response = self::$staticApplicationClient->makeSignInPageWriteRequest('user@example.com', 'password');
        $responseCookie = Cookie::fromString($response->getHeaderLine('set-cookie'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertSame('token', $responseCookie->getName());
        self::assertSame('/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());
    }
}
