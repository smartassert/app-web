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
        $response = self::$staticApplicationClient->makeSignInPageReadRequest(null, $method);

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
}
