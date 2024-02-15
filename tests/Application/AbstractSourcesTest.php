<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Enum\Routes;
use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use App\Tests\Services\CredentialsFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractSourcesTest extends AbstractApplicationTestCase
{
    public function testGetInvalidToken(): void
    {
        $this->kernelBrowser->getCookieJar()->clear();

        $response = $this->applicationClient->makeSourcesReadRequest(null);
        self::assertSame(302, $response->getStatusCode());

        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        \assert($urlGenerator instanceof UrlGeneratorInterface);

        $redirectRouteSerializer = self::getContainer()->get(Serializer::class);
        \assert($redirectRouteSerializer instanceof Serializer);

        $expectedRedirectRoute = new RedirectRoute(Routes::SOURCES_NAME->value, []);
        $expected = $urlGenerator->generate(
            Routes::SIGN_IN_VIEW_NAME->value,
            ['route' => $redirectRouteSerializer->serialize($expectedRedirectRoute)]
        );

        self::assertSame($expected, $response->getHeaderLine('location'));
    }

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
