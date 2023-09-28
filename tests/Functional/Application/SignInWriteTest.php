<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractSignInWriteTest;
use App\Tests\Services\SessionHandler;
use Symfony\Component\HttpFoundation\Cookie;

class SignInWriteTest extends AbstractSignInWriteTest
{
    use GetClientAdapterTrait;

    public function testWriteUnauthorized(): void
    {
        $response = self::$staticApplicationClient->makeSignInPageWriteRequest(null, null);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertSame('/sign-in/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());

        $responseCookieValue = $response->getHeaderLine('set-cookie');
        if ('' !== $responseCookieValue) {
            $responseCookie = Cookie::fromString($response->getHeaderLine('set-cookie'));
            self::assertNotSame('token', $responseCookie->getName());
        }
    }

    /**
     * @dataProvider writeInvalidCredentialsDataProvider
     */
    public function testWriteInvalidCredentials(
        ?string $userIdentifier,
        ?string $password,
        string $expectedResponseHeaderLocation,
        string $expectedFlashKey
    ): void {
        $sessionHandler = self::getContainer()->get(SessionHandler::class);
        \assert($sessionHandler instanceof SessionHandler);

        $session = $sessionHandler->create();
        $sessionHandler->persist(self::$kernelBrowser, $session);

        $response = self::$staticApplicationClient->makeSignInPageWriteRequest($userIdentifier, $password);

        self::assertSame($expectedResponseHeaderLocation, $response->getHeaderLine('location'));
        self::assertTrue($session->getFlashBag()->has($expectedFlashKey));
    }

    /**
     * @return array<mixed>
     */
    public function writeInvalidCredentialsDataProvider(): array
    {
        return [
            'empty user-identifier, empty password' => [
                'userIdentifier' => null,
                'password' => null,
                'expectedResponseHeaderLocation' => '/sign-in/',
                'expectedFlashKey' => 'empty-user-identifier',
            ],
            'non-empty user-identifier, empty password' => [
                'userIdentifier' => 'user@example.com',
                'password' => null,
                'expectedResponseHeaderLocation' => '/sign-in/?email=user%40example.com',
                'expectedFlashKey' => 'empty-password',
            ],
            'empty user-identifier, non-empty password' => [
                'userIdentifier' => null,
                'password' => null,
                'expectedResponseHeaderLocation' => '/sign-in/',
                'expectedFlashKey' => 'empty-user-identifier',
            ],
            'unauthorized' => [
                'userIdentifier' => 'user@example.com',
                'password' => 'invalid',
                'expectedResponseHeaderLocation' => '/sign-in/?email=user%40example.com',
                'expectedFlashKey' => 'unauthorized',
            ],
        ];
    }
}
