<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\Routes;
use App\Response\RedirectResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class LogoutController
{
    public function __construct(
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/logout/', name: Routes::LOG_OUT_NAME->value, methods: ['POST'])]
    public function handle(): Response
    {
        $response = $this->security->logout(validateCsrfToken: false);
        if (null === $response) {
            $response = new RedirectResponse($this->urlGenerator->generate(Routes::SIGN_IN_VIEW_NAME->value));
        }

        return $response;
    }
}
