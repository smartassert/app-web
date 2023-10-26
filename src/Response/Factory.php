<?php

declare(strict_types=1);

namespace App\Response;

use App\Enum\Routes;
use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class Factory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Serializer $redirectRouteSerializer,
    ) {
    }

    public function createSignInRedirectResponse(?string $userIdentifier, ?RedirectRoute $route): RedirectResponse
    {
        $urlParameters = [];
        if (is_string($userIdentifier) && '' !== $userIdentifier) {
            $urlParameters['email'] = $userIdentifier;
        }

        if ($route instanceof RedirectRoute) {
            $urlParameters['route'] = $this->redirectRouteSerializer->serialize($route);
        }

        return new RedirectResponse(
            $this->urlGenerator->generate(Routes::SIGN_IN_VIEW_NAME->value, $urlParameters)
        );
    }

    public function createDashboardRedirectResponse(): RedirectResponse
    {
        return new RedirectResponse(
            $this->urlGenerator->generate(Routes::DASHBOARD_NAME->value)
        );
    }
}
