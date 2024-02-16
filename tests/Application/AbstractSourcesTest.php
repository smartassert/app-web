<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\CredentialsFactory;

abstract class AbstractSourcesTest extends AbstractApplicationTestCase
{
    public function testGetSuccess(): void
    {
        $credentialsFactory = self::getContainer()->get(CredentialsFactory::class);
        \assert($credentialsFactory instanceof CredentialsFactory);

        $credentials = $credentialsFactory->create($this->applicationClient, $this->getSessionIdentifier());

        $response = $this->applicationClient->makeSourcesReadRequest($credentials);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));

        $credentials = $credentialsFactory->createFromResponse($response, $this->getSessionIdentifier());
        $response = $this->applicationClient->makeSourcesReadRequest($credentials);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }
}
