<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Enum\SignInErrorState;
use App\RedirectRoute\Factory;
use App\RedirectRoute\Serializer;
use App\Tests\Application\AbstractSignInWriteTest;
use App\Tests\Services\SessionHandler;

class SignInWriteTest extends AbstractSignInWriteTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;

    /**
     * @dataProvider writeInvalidCredentialsDataProvider
     *
     * @param callable(Factory, Serializer): string $expectedLocationCreator
     */
    public function testWriteInvalidCredentials(
        ?string $userIdentifier,
        ?string $password,
        callable $expectedLocationCreator,
        string $expectedError,
    ): void {
        $sessionHandler = self::getContainer()->get(SessionHandler::class);
        \assert($sessionHandler instanceof SessionHandler);

        $session = $sessionHandler->create();
        $sessionHandler->persist($this->kernelBrowser, $session);

        $redirectRouteFactory = self::getContainer()->get(Factory::class);
        \assert($redirectRouteFactory instanceof Factory);

        $redirectRouteSerializer = self::getContainer()->get(Serializer::class);
        \assert($redirectRouteSerializer instanceof Serializer);

        $response = $this->applicationClient->makeSignInPageWriteRequest($userIdentifier, $password);

        self::assertSame(
            $expectedLocationCreator($redirectRouteFactory, $redirectRouteSerializer),
            $response->getHeaderLine('location')
        );

        self::assertTrue($session->getFlashBag()->has('error'));
        $error = $session->getFlashBag()->get('error')[0];
        self::assertSame($expectedError, $error);
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

                    return '/sign-in/?route=' . $serializedRedirectRoute;
                },
                'expectedError' => SignInErrorState::EMAIL_EMPTY->value,
            ],
            'non-empty user-identifier, empty password' => [
                'userIdentifier' => 'user@example.com',
                'password' => null,
                'expectedLocationCreator' => function (
                    Factory $redirectRouteFactory,
                    Serializer $redirectRouteSerializer,
                ) {
                    $serializedRedirectRoute = $redirectRouteSerializer->serialize($redirectRouteFactory->getDefault());

                    return '/sign-in/?email=user@example.com&route=' . $serializedRedirectRoute;
                },
                'expectedError' => SignInErrorState::PASSWORD_EMPTY->value,
            ],
            'empty user-identifier, non-empty password' => [
                'userIdentifier' => null,
                'password' => null,
                'expectedLocationCreator' => function (
                    Factory $redirectRouteFactory,
                    Serializer $redirectRouteSerializer,
                ) {
                    $serializedRedirectRoute = $redirectRouteSerializer->serialize($redirectRouteFactory->getDefault());

                    return '/sign-in/?route=' . $serializedRedirectRoute;
                },
                'expectedError' => SignInErrorState::EMAIL_EMPTY->value,
            ],
        ];
    }
}
