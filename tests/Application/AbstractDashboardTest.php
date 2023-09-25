<?php

declare(strict_types=1);

namespace App\Tests\Application;

abstract class AbstractDashboardTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testGetBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeDashboardReadRequest($method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createBadMethodDataProvider(): array
    {
        return [
            'POST' => [
                'method' => 'POST',
            ],
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'DELETE',
            ],
        ];
    }

    public function testGetSuccess(): void
    {
        $response = self::$staticApplicationClient->makeDashboardReadRequest();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }
}
