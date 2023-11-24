<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\Routes;
use App\Response\RedirectResponseFactory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class LogoutController
{
    public function __construct(
        private Security $security,
        private RedirectResponseFactory $redirectResponseFactory,
    ) {
    }

    #[Route('/logout/', name: Routes::LOG_OUT_NAME->value, methods: ['POST'])]
    public function handle(): Response
    {
        $response = $this->security->logout(validateCsrfToken: false);
        if (null === $response) {
            $response = $this->redirectResponseFactory->createForSignIn(null, null);
        }

        return $response;
    }
}
