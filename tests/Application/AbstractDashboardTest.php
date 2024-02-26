<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\Credentials;

abstract class AbstractDashboardTest extends AbstractApplicationTestCase
{
    public function testGetSuccess(): void
    {
        $credentials = self::getContainer()->get(Credentials::class);
        \assert($credentials instanceof Credentials);

        $credentials->create($this->applicationClient, $this->getSessionIdentifier());

        $response = $this->applicationClient->makeDashboardReadRequest($credentials);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));

        $credentials->refresh($response, $this->getSessionIdentifier());
        $response = $this->applicationClient->makeDashboardReadRequest($credentials);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }
}
