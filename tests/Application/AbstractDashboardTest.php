<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\RedirectRoute\RedirectRoute;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractDashboardTest extends AbstractApplicationTestCase
{
    public function testGetInvalidToken(): void
    {
        $response = self::$staticApplicationClient->makeDashboardReadRequest(md5((string) rand()));

        self::assertSame(302, $response->getStatusCode());

        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        \assert($urlGenerator instanceof UrlGeneratorInterface);

        $expectedRedirectRoute = new RedirectRoute('dashboard', []);
        $expected = $urlGenerator->generate('sign_in_view', ['route' => $expectedRedirectRoute->serialize()]);

        self::assertSame($expected, $response->getHeaderLine('location'));
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
