<?php

declare(strict_types=1);

namespace App\Tests\Application;

abstract class AbstractSourcesTest extends AbstractApplicationTestCase
{
    public function testGetSuccess(): void
    {
        $response = $this->applicationClient->makeSourcesReadRequest();
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));

        $response = $this->applicationClient->makeSourcesReadRequest();
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }
}
