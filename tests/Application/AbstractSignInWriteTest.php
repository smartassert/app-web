<?php

declare(strict_types=1);

namespace App\Tests\Application;

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
        $response = self::$staticApplicationClient->makeSignInPageWriteRequest(null, null);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertSame('', $response->getHeaderLine('set-cookie'));
        self::assertSame('/sign-in/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());
    }

    public function testWriteSuccess(): void
    {
        $response = self::$staticApplicationClient->makeSignInPageWriteRequest('user@example.com', 'password');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertNotEmpty($response->getHeaderLine('set-cookie'));
        self::assertSame('/sign-in/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());
    }
}
