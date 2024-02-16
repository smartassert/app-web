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
        $response = $this->applicationClient->makeSignInPageWriteRequest(null, null, $method);

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
        $response = $this->applicationClient->makeSignInPageWriteRequest('user@example.com', 'invalid');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertStringContainsString('/sign-in/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());

        $responseCookieValue = $response->getHeaderLine('set-cookie');
        if ('' === $responseCookieValue) {
            return;
        }

        $responseCookie = Cookie::fromString($response->getHeaderLine('set-cookie'));
        self::assertSame('token', $responseCookie->getName());
        self::assertSame('deleted', $responseCookie->getValue());
        self::assertSame(0, $responseCookie->getMaxAge());
    }

    public function testWriteSuccess(): void
    {
        $response = $this->applicationClient->makeSignInPageWriteRequest('user@example.com', 'password');
        $responseCookie = Cookie::fromString($response->getHeaderLine('set-cookie'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertSame('token', $responseCookie->getName());
        self::assertSame('/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());
    }
}
