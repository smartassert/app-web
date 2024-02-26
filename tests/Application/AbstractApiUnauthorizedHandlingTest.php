<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\ApplicationClient\Client;
use App\Tests\Services\Credentials;
use App\Tests\Services\DataRepository;
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

        $credentials = self::getContainer()->get(Credentials::class);
        \assert($credentials instanceof Credentials);

        $credentials->create($this->applicationClient, $this->getSessionIdentifier());

        $response = $successfulAction($this->applicationClient, (string) $credentials);
        self::assertSame(200, $response->getStatusCode());

        $usersDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=users;user=postgres;password=password!'
        );
        $usersDataRepository->getConnection()->query('delete from public.api_key');

        $response = $failureAction($this->applicationClient, (string) $credentials);
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
            'dashboard' => [
                'successfulAction' => function (Client $applicationClient, string $credentials) {
                    return $applicationClient->makeDashboardReadRequest($credentials);
                },
                'failureAction' => function (Client $applicationClient, string $credentials) {
                    return $applicationClient->makeDashboardReadRequest($credentials);
                },
            ],
            'sources' => [
                'successfulAction' => function (Client $applicationClient, string $credentials) {
                    return $applicationClient->makeSourcesReadRequest($credentials);
                },
                'failureAction' => function (Client $applicationClient, string $credentials) {
                    return $applicationClient->makeSourcesReadRequest($credentials);
                },
            ],
            'add file source' => [
                'successfulAction' => function (Client $applicationClient, string $credentials) {
                    return $applicationClient->makeSourcesReadRequest($credentials);
                },
                'failureAction' => function (Client $applicationClient, string $credentials) {
                    return $applicationClient->makeFileSourceAddRequest($credentials, md5((string) rand()));
                },
            ],
        ];
    }
}
