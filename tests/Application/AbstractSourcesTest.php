<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Enum\Routes;
use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use App\Tests\Services\RequestCookieFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractSourcesTest extends AbstractApplicationTestCase
{
    public function testGetInvalidToken(): void
    {
        $this->kernelBrowser->getCookieJar()->clear();

        $response = $this->applicationClient->makeSourcesReadRequest('token=invalid');
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
        $requestCookieFactory = self::getContainer()->get(RequestCookieFactory::class);
        \assert($requestCookieFactory instanceof RequestCookieFactory);

        $requestCookie = $requestCookieFactory->create($this->applicationClient, $this->getSessionIdentifier());

        $response = $this->applicationClient->makeSourcesReadRequest($requestCookie);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));

        $requestCookie = $requestCookieFactory->createFromResponse($response, $this->getSessionIdentifier());
        $response = $this->applicationClient->makeSourcesReadRequest($requestCookie);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }
}
