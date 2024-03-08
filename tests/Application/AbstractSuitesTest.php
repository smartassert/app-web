<?php

declare(strict_types=1);

namespace App\Tests\Application;

abstract class AbstractSuitesTest extends AbstractApplicationTestCase
{
    public function testGetSuccess(): void
    {
        $response = $this->applicationClient->makeSuitesReadRequest();
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }
}
