<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\CredentialsStore;

abstract class AbstractDashboardTest extends AbstractApplicationTestCase
{
    public function testGetSuccess(): void
    {
        $credentialsStore = self::getContainer()->get(CredentialsStore::class);
        \assert($credentialsStore instanceof CredentialsStore);

        $credentialsStore->create($this->applicationClient, $this->getSessionIdentifier());

        $response = $this->applicationClient->makeDashboardReadRequest((string) $credentialsStore);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));

        $credentialsStore->refresh($response, $this->getSessionIdentifier());
        $response = $this->applicationClient->makeDashboardReadRequest((string) $credentialsStore);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }
}
