<?php

declare(strict_types=1);

namespace App\Tests\Application;

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
        $response = $this->applicationClient->makeSignInPageReadRequest(
            credentials: $this->applicationClient->getCredentials()
        );

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertSame('/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());
    }
}
