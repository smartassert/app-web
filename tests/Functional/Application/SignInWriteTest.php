<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractSignInWriteTest;

class SignInWriteTest extends AbstractSignInWriteTest
{
    use GetClientAdapterTrait;

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
