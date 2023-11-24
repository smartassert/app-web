<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\Routes;
use App\Response\RedirectResponseFactory;
use App\Security\User;
use SmartAssert\ApiClient\UsersClient;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class LogoutController
{
    public function __construct(
        private Security $security,
        private UsersClient $usersClient,
        private RedirectResponseFactory $redirectResponseFactory,
    ) {
    }

    #[Route('/logout/', name: Routes::LOG_OUT_NAME->value, methods: ['POST'])]
    public function handle(Request $request): Response
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
            $response = $this->redirectResponseFactory->createForSignIn(null, null);
        }

        $session = $request->getSession();
        $session->clear();

        return $response;
    }
}
