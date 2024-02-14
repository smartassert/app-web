<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractDashboardTest;
use App\Tests\Services\DataRepository;
use App\Tests\Services\RequestCookieFactory;

class DashboardTest extends AbstractDashboardTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;

    public function testGetDashboardApiUnauthorized(): void
    {
        $requestCookieFactory = self::getContainer()->get(RequestCookieFactory::class);
        \assert($requestCookieFactory instanceof RequestCookieFactory);

        $requestCookie = $requestCookieFactory->create($this->applicationClient, $this->getSessionIdentifier());

        $response = $this->applicationClient->makeDashboardReadRequest($requestCookie);
        self::assertSame(200, $response->getStatusCode());

        $usersDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=users;user=postgres;password=password!'
        );
        $usersDataRepository->getConnection()->query('delete from public.api_key');

        $response = $this->applicationClient->makeDashboardReadRequest($requestCookie);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame(
            '/sign-in/?email=user@example.com&route=eyJuYW1lIjoiZGFzaGJvYXJkIiwicGFyYW1ldGVycyI6W119',
            $response->getHeaderLine('location')
        );
    }
}
