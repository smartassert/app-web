<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\AbstractDashboardTest;
use SmartAssert\ApiClient\Model\RefreshableToken;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;

class DashboardTest extends AbstractDashboardTest
{
    use GetClientAdapterTrait;

    public function testExpiredUserTokenIsRefreshed(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);

        $frontendToken = $frontendTokenProvider->get('user@example.com');

        $response = self::$staticApplicationClient->makeDashboardReadRequest(new RefreshableToken(
            $frontendToken->token,
            $frontendToken->refreshToken
        ));

        self::assertSame(200, $response->getStatusCode());

        $jwtTokenTtl = $this->getUsersServiceJwtTokenTtl();
        $waitTime = $jwtTokenTtl + 1;

        sleep($waitTime);

        $response = self::$staticApplicationClient->makeDashboardReadRequest(new RefreshableToken(
            $frontendToken->token,
            $frontendToken->refreshToken
        ));

        self::assertSame(200, $response->getStatusCode());
    }

    private function getUsersServiceJwtTokenTtl(): int
    {
        $jwtTokenEnvVarName = 'JWT_TOKEN_TTL';

        $usersServicePrintEnvOutput =
            (string) shell_exec(sprintf(
                'docker-compose -f tests/build/docker-compose.yml exec users-service printenv | grep %s',
                $jwtTokenEnvVarName
            ));

        return (int) str_replace($jwtTokenEnvVarName . '=', '', $usersServicePrintEnvOutput);
    }
}
