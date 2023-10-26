<?php

declare(strict_types=1);

namespace App\Controller;

use App\Response\Factory;
use App\Security\User;
use SmartAssert\ApiClient\UsersClient;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

readonly class LogoutController
{
    public function __construct(
        private Security $security,
        private UsersClient $usersClient,
        private Factory $signInRedirectResponseFactory,
    ) {
    }

    public function handle(): Response
    {
        $user = $this->security->getUser();
        if ($user instanceof User) {
            try {
                $this->usersClient->revokeRefreshToken(
                    $user->getSecurityToken()->token,
                    $user->getSecurityToken()->refreshToken
                );
            } catch (\Throwable) {
                // Intentionally ignore all exceptions
            }
        }

        $response = $this->security->logout(validateCsrfToken: false);
        if (null === $response) {
            $response = $this->signInRedirectResponseFactory->create(null, null);
        }

        return $response;
    }
}
