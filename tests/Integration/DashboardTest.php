<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\AbstractDashboardTest;

class DashboardTest extends AbstractDashboardTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;

    public function testExpiredUserTokenIsRefreshed(): void
    {
        $response = $this->applicationClient->makeDashboardReadRequest();
        self::assertSame(200, $response->getStatusCode());

        $jwtTokenTtl = $this->getUsersServiceJwtTokenTtl();
        $waitTime = $jwtTokenTtl + 1;

        sleep($waitTime);

        $response = $this->applicationClient->makeDashboardReadRequest();
        self::assertSame(200, $response->getStatusCode());
    }

    private function getUsersServiceJwtTokenTtl(): int
    {
        $jwtTokenEnvVarName = 'JWT_TOKEN_TTL';

        $usersServicePrintEnvOutput =
            (string) shell_exec(sprintf(
                'docker compose -f tests/build/docker-compose.yml exec users-service printenv | grep %s',
                $jwtTokenEnvVarName
            ));

        return (int) str_replace($jwtTokenEnvVarName . '=', '', $usersServicePrintEnvOutput);
    }
}
