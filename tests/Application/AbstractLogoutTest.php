<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Enum\Routes;
use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use App\Tests\Services\RequestCookieFactory;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Cookie;

abstract class AbstractLogoutTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider badMethodDataProvider
     */
    public function testReadBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeLogoutRequest('', $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function badMethodDataProvider(): array
    {
        return [
            'DELETE' => [
                'method' => 'DELETE',
            ],
            'GET' => [
                'method' => 'GET',
            ],
            'PUT' => [
                'method' => 'PUT',
            ],
        ];
    }

    public function testLogoutSuccessWithoutAuthentication(): void
    {
        $redirectRouteSerializer = self::getContainer()->get(Serializer::class);
        \assert($redirectRouteSerializer instanceof Serializer);

        $expectedRedirectRoute = new RedirectRoute(Routes::SIGN_IN_VIEW_NAME->value, []);
        $expectedLocation = '/sign-in/?route=' . $redirectRouteSerializer->serialize($expectedRedirectRoute);

        $response = self::$staticApplicationClient->makeLogoutRequest();

        $this->assertLogoutSuccessResponse($response, $expectedLocation);
    }

    public function testLogoutSuccessWithAuthentication(): void
    {
        $requestCookieFactory = self::getContainer()->get(RequestCookieFactory::class);
        \assert($requestCookieFactory instanceof RequestCookieFactory);

        $requestCookie = $requestCookieFactory->create(self::$staticApplicationClient, $this->getSessionIdentifier());

        $response = self::$staticApplicationClient->makeLogoutRequest($requestCookie);

        $this->assertLogoutSuccessResponse($response, '/sign-in/');
    }

    private function assertLogoutSuccessResponse(ResponseInterface $response, string $expectedLocation): void
    {
        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertSame('', $response->getBody()->getContents());
        self::assertSame($expectedLocation, $response->getHeaderLine('location'));

        $responseCookieValue = $response->getHeaderLine('set-cookie');
        if ('' !== $responseCookieValue) {
            $responseCookie = Cookie::fromString($response->getHeaderLine('set-cookie'));

            self::assertSame('token', $responseCookie->getName());
            self::assertSame('deleted', $responseCookie->getValue());
        }
    }
}
