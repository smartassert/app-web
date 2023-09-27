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
     * @dataProvider writeEmptyFieldDataProvider
     */
    public function testWriteEmptyField(?string $userIdentifier, ?string $password, ?string $expected): void
    {
        $sessionHandler = self::getContainer()->get(SessionHandler::class);
        \assert($sessionHandler instanceof SessionHandler);

        $session = $sessionHandler->create();
        $sessionHandler->persist(self::$kernelBrowser, $session);

        self::$staticApplicationClient->makeSignInPageWriteRequest('user@example.com', null);

        self::assertTrue($session->getFlashBag()->has('empty-password'));
    }

    /**
     * @return array<mixed>
     */
    public function writeEmptyFieldDataProvider(): array
    {
        return [
            'empty user-identifier, empty password' => [
                'userIdentifier' => null,
                'password' => null,
                'expected' => 'empty-user-identifier',
            ],
            'non-empty user-identifier, empty password' => [
                'userIdentifier' => 'user@example.com',
                'password' => null,
                'expected' => 'empty-password',
            ],
            'empty user-identifier, non-empty password' => [
                'userIdentifier' => null,
                'password' => null,
                'expected' => 'empty-user-identifier',
            ],
        ];
    }
}
