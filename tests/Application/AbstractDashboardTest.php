<?php

declare(strict_types=1);

namespace App\Tests\Application;

use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractDashboardTest extends AbstractApplicationTestCase
{
    public function testGetInvalidToken(): void
    {
        $response = self::$staticApplicationClient->makeDashboardReadRequest(md5((string) rand()));

        self::assertSame(302, $response->getStatusCode());

        $router = self::getContainer()->get(RouterInterface::class);
        \assert($router instanceof RouterInterface);

        self::assertSame($router->generate('sign_in_view'), $response->getHeaderLine('location'));
    }

    public function testGetSuccess(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);

        $frontendToken = $frontendTokenProvider->get('user@example.com');
        $response = self::$staticApplicationClient->makeDashboardReadRequest($frontendToken->token);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }
}
