<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\RequestCookieFactory;

abstract class AbstractSignInReadTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testReadBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeSignInPageReadRequest(method: $method);

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
        $response = $this->applicationClient->makeSignInPageReadRequest();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }

    public function testReadWhenSignedInRedirectsToDashboard(): void
    {
        $requestCookieFactory = self::getContainer()->get(RequestCookieFactory::class);
        \assert($requestCookieFactory instanceof RequestCookieFactory);

        $requestCookie = $requestCookieFactory->create($this->applicationClient, $this->getSessionIdentifier());

        $response = $this->applicationClient->makeSignInPageReadRequest(cookie: $requestCookie);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertSame('/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());
    }
}
