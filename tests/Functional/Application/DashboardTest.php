<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractDashboardTest;
use App\Tests\Services\CredentialsFactory;
use App\Tests\Services\DataRepository;

class DashboardTest extends AbstractDashboardTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;

    public function testGetDashboardApiUnauthorized(): void
    {
        $credentialsFactory = self::getContainer()->get(CredentialsFactory::class);
        \assert($credentialsFactory instanceof CredentialsFactory);

        $credentials = $credentialsFactory->create($this->applicationClient, $this->getSessionIdentifier());

        $response = $this->applicationClient->makeDashboardReadRequest($credentials);
        self::assertSame(200, $response->getStatusCode());

        $usersDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=users;user=postgres;password=password!'
        );
        $usersDataRepository->getConnection()->query('delete from public.api_key');

        $response = $this->applicationClient->makeDashboardReadRequest($credentials);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame(
            '/sign-in/?email=user@example.com&route=eyJuYW1lIjoiZGFzaGJvYXJkIiwicGFyYW1ldGVycyI6W119',
            $response->getHeaderLine('location')
        );
    }
}
