<?php

declare(strict_types=1);

namespace App\Tests\Application;

abstract class AbstractDashboardTest extends AbstractApplicationTestCase
{
    public function testGetSuccess(): void
    {
        $response = $this->applicationClient->makeDashboardReadRequest();
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }
}
