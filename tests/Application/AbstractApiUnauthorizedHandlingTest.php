<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Model\Credentials;
use App\Tests\Services\ApplicationClient\Client;
use App\Tests\Services\CredentialsStore;
use App\Tests\Services\DataRepository;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractApiUnauthorizedHandlingTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider handleApiUnauthorizedExceptionDataProvider
     *
     * @param callable(Client, Credentials): ResponseInterface $successfulAction
     * @param callable(Client, Credentials): ResponseInterface $failureAction
     */
    public function testHandleApiUnauthorizedException(callable $successfulAction, callable $failureAction): void
    {
        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        \assert($urlGenerator instanceof UrlGeneratorInterface);

        $credentialsStore = self::getContainer()->get(CredentialsStore::class);
        \assert($credentialsStore instanceof CredentialsStore);

        $credentialsStore->create($this->applicationClient, $this->getSessionIdentifier());

        $response = $successfulAction($this->applicationClient, $credentialsStore->get());
        self::assertSame(200, $response->getStatusCode());

        $usersDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=users;user=postgres;password=password!'
        );
        $usersDataRepository->getConnection()->query('delete from public.api_key');

        $response = $failureAction($this->applicationClient, $credentialsStore->get());
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
                'successfulAction' => function (Client $applicationClient, Credentials $cookie) {
                    return $applicationClient->makeDashboardReadRequest($cookie);
                },
                'failureAction' => function (Client $applicationClient, Credentials $cookie) {
                    return $applicationClient->makeDashboardReadRequest($cookie);
                },
            ],
            'sources' => [
                'successfulAction' => function (Client $applicationClient, Credentials $cookie) {
                    return $applicationClient->makeSourcesReadRequest($cookie);
                },
                'failureAction' => function (Client $applicationClient, Credentials $cookie) {
                    return $applicationClient->makeSourcesReadRequest($cookie);
                },
            ],
            'add file source' => [
                'successfulAction' => function (Client $applicationClient, Credentials $cookie) {
                    return $applicationClient->makeSourcesReadRequest($cookie);
                },
                'failureAction' => function (Client $applicationClient, Credentials $cookie) {
                    return $applicationClient->makeFileSourceAddRequest($cookie, md5((string) rand()));
                },
            ],
        ];
    }
}
