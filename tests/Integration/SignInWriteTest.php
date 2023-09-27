<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\AbstractSignInWriteTest;
use Symfony\Component\HttpFoundation\Cookie;

class SignInWriteTest extends AbstractSignInWriteTest
{
    use GetClientAdapterTrait;

    public function testWriteUnauthorized(): void
    {
        $response = self::$staticApplicationClient->makeSignInPageWriteRequest(null, null);
        $responseCookie = Cookie::fromString($response->getHeaderLine('set-cookie'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('', $response->getHeaderLine('content-type'));
        self::assertNotSame('token', $responseCookie->getName());
        self::assertSame('/sign-in/', $response->getHeaderLine('location'));
        self::assertSame('', $response->getBody()->getContents());
    }
}
