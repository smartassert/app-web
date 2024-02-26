<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\ApplicationClient\Client;
use App\Tests\Services\DataRepository\DataRepository;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractApiUnauthorizedHandlingTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider handleApiUnauthorizedExceptionDataProvider
     *
     * @param callable(Client): ResponseInterface $successfulAction
     * @param callable(Client): ResponseInterface $failureAction
     */
    public function testHandleApiUnauthorizedException(callable $successfulAction, callable $failureAction): void
    {
        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        \assert($urlGenerator instanceof UrlGeneratorInterface);

        $response = $successfulAction($this->applicationClient);
        self::assertSame(200, $response->getStatusCode());

        $usersDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=users;user=postgres;password=password!'
        );
        $usersDataRepository->getConnection()->query('delete from public.api_key');

        $response = $failureAction($this->applicationClient);
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
                'successfulAction' => function (Client $applicationClient) {
                    return $applicationClient->makeDashboardReadRequest();
                },
                'failureAction' => function (Client $applicationClient) {
                    return $applicationClient->makeDashboardReadRequest();
                },
            ],
            'sources' => [
                'successfulAction' => function (Client $applicationClient) {
                    return $applicationClient->makeSourcesReadRequest();
                },
                'failureAction' => function (Client $applicationClient) {
                    return $applicationClient->makeSourcesReadRequest();
                },
            ],
            'add file source' => [
                'successfulAction' => function (Client $applicationClient) {
                    return $applicationClient->makeSourcesReadRequest();
                },
                'failureAction' => function (Client $applicationClient) {
                    return $applicationClient->makeFileSourceAddRequest(md5((string) rand()));
                },
            ],
        ];
    }
}
