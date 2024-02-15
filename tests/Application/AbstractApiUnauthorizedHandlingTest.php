<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\ApplicationClient\Client;
use App\Tests\Services\DataRepository;
use App\Tests\Services\RequestCookieFactory;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractApiUnauthorizedHandlingTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider handleApiUnauthorizedExceptionDataProvider
     *
     * @param callable(Client, string): ResponseInterface $successfulAction
     * @param callable(Client, string): ResponseInterface $failureAction
     */
    public function testHandleApiUnauthorizedException(callable $successfulAction, callable $failureAction): void
    {
        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        \assert($urlGenerator instanceof UrlGeneratorInterface);

        $requestCookieFactory = self::getContainer()->get(RequestCookieFactory::class);
        \assert($requestCookieFactory instanceof RequestCookieFactory);

        $requestCookie = $requestCookieFactory->create($this->applicationClient, $this->getSessionIdentifier());

        $response = $successfulAction($this->applicationClient, $requestCookie);

        self::assertSame(200, $response->getStatusCode());

        $usersDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=users;user=postgres;password=password!'
        );
        $usersDataRepository->getConnection()->query('delete from public.api_key');

        $response = $failureAction($this->applicationClient, $requestCookie);

        echo $response->getBody()->getContents();

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(
            '/sign-in/?email=user@example.com&route=eyJuYW1lIjoiZGFzaGJvYXJkIiwicGFyYW1ldGVycyI6W119',
            $response->getHeaderLine('location')
        );
    }

    /**
     * @return array<mixed>
     */
    public function handleApiUnauthorizedExceptionDataProvider(): array
    {
        return [
            'read sources' => [
                'successfulAction' => function (Client $applicationClient, string $requestCookie) {
                    return $applicationClient->makeSourcesReadRequest($requestCookie);
                },
                'failureAction' => function (Client $applicationClient, string $requestCookie) {
                    return $applicationClient->makeSourcesReadRequest($requestCookie);
                },
            ],
            'add file source' => [
                'successfulAction' => function (Client $applicationClient, string $requestCookie) {
                    return $applicationClient->makeSourcesReadRequest($requestCookie);
                },
                'failureAction' => function (Client $applicationClient, string $requestCookie) {
                    return $applicationClient->makeFileSourceAddRequest($requestCookie, md5((string) rand()));
                },
            ],
        ];
    }
}
