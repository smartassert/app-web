<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\RedirectRoute\Factory;
use App\RedirectRoute\Serializer;
use App\Tests\Application\AbstractSignInWriteTest;

class SignInWriteTest extends AbstractSignInWriteTest
{
    use GetClientAdapterTrait;

    /**
     * @dataProvider writeInvalidCredentialsDataProvider
     *
     * @param callable(Factory, Serializer): string $expectedLocationCreator
     */
    public function testWriteInvalidCredentials(
        ?string $userIdentifier,
        ?string $password,
        callable $expectedLocationCreator,
    ): void {
        $redirectRouteFactory = self::getContainer()->get(Factory::class);
        \assert($redirectRouteFactory instanceof Factory);

        $redirectRouteSerializer = self::getContainer()->get(Serializer::class);
        \assert($redirectRouteSerializer instanceof Serializer);

        $response = self::$staticApplicationClient->makeSignInPageWriteRequest($userIdentifier, $password);

        self::assertSame(
            $expectedLocationCreator($redirectRouteFactory, $redirectRouteSerializer),
            $response->getHeaderLine('location')
        );
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
                'expectedLocationCreator' => function (
                    Factory $redirectRouteFactory,
                    Serializer $redirectRouteSerializer,
                ) {
                    $serializedRedirectRoute = $redirectRouteSerializer->serialize($redirectRouteFactory->getDefault());

                    return '/sign-in/?error=email_empty&route=' . $serializedRedirectRoute;
                },
            ],
            'non-empty user-identifier, empty password' => [
                'userIdentifier' => 'user@example.com',
                'password' => null,
                'expectedLocationCreator' => function (
                    Factory $redirectRouteFactory,
                    Serializer $redirectRouteSerializer,
                ) {
                    $serializedRedirectRoute = $redirectRouteSerializer->serialize($redirectRouteFactory->getDefault());

                    return '/sign-in/?email=user@example.com&error=password_empty&route=' . $serializedRedirectRoute;
                },
            ],
            'empty user-identifier, non-empty password' => [
                'userIdentifier' => null,
                'password' => null,
                'expectedLocationCreator' => function (
                    Factory $redirectRouteFactory,
                    Serializer $redirectRouteSerializer,
                ) {
                    $serializedRedirectRoute = $redirectRouteSerializer->serialize($redirectRouteFactory->getDefault());

                    return '/sign-in/?error=email_empty&route=' . $serializedRedirectRoute;
                },
            ],
        ];
    }
}
