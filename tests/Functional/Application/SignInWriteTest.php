<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractSignInWriteTest;
use Symfony\Component\HttpFoundation\Cookie;

class SignInWriteTest extends AbstractSignInWriteTest
{
    use GetClientAdapterTrait;

    public function testWriteUnauthorized(): void
    {
        $response = self::$staticApplicationClient->makeSignInPageWriteRequest('user@example.com', 'invalid');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertStringContainsString('/sign-in/', $response->getHeaderLine('location'));
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
    ): void {
        $response = self::$staticApplicationClient->makeSignInPageWriteRequest($userIdentifier, $password);

        self::assertSame($expectedResponseHeaderLocation, $response->getHeaderLine('location'));
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
                'expectedResponseHeaderLocation' => '/sign-in/?error=email_empty',
            ],
            'non-empty user-identifier, empty password' => [
                'userIdentifier' => 'user@example.com',
                'password' => null,
                'expectedResponseHeaderLocation' => '/sign-in/?email=user@example.com&error=password_empty',
            ],
            'empty user-identifier, non-empty password' => [
                'userIdentifier' => null,
                'password' => null,
                'expectedResponseHeaderLocation' => '/sign-in/?error=email_empty',
            ],
        ];
    }
}
