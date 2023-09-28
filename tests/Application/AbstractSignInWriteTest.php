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

    public function testWriteSuccess(): void
    {
        $response = self::$staticApplicationClient->makeSignInPageWriteRequest('user@example.com', 'password');
        $responseCookie = Cookie::fromString($response->getHeaderLine('set-cookie'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertSame('token', $responseCookie->getName());
        self::assertSame('/sign-in/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());
    }
}
