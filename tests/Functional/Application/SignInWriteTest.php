<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\RedirectRoute\Factory;
use App\RedirectRoute\Serializer;
use App\Tests\Application\AbstractSignInWriteTest;
use App\Tests\Assertions\SymfonyRedirectResponseAssertionTrait;

class SignInWriteTest extends AbstractSignInWriteTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
    use SymfonyRedirectResponseAssertionTrait;

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
        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: '/sign-in/'
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());

        $signInForm = $crawler->filter('input[type=submit]')->form([
            'user-identifier' => $userIdentifier,
            'password' => $password,
        ]);

        $this->kernelBrowser->submit($signInForm);

        $redirectRouteFactory = self::getContainer()->get(Factory::class);
        \assert($redirectRouteFactory instanceof Factory);

        $redirectRouteSerializer = self::getContainer()->get(Serializer::class);
        \assert($redirectRouteSerializer instanceof Serializer);

        $response = $this->kernelBrowser->getResponse();

        self::assertSame(302, $response->getStatusCode());
        $this->assertSymfonyRedirectResponse(
            $response,
            $expectedLocationCreator($redirectRouteFactory, $redirectRouteSerializer)
        );

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: (string) $response->headers->get('location')
        );

        $response = $this->kernelBrowser->getResponse();
        self::assertSame(200, $response->getStatusCode());

        $errorContainer = $crawler->filter('div.error');
        self::assertCount(1, $errorContainer);
        self::assertSame($expectedError, $errorContainer->text());
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
                'expectedError' => 'Email address empty!',
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
                'expectedError' => 'Password empty!',
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
                'expectedError' => 'Email address empty!',
            ],
        ];
    }
}
