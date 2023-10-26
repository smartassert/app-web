<?php

declare(strict_types=1);

namespace App\Response;

use App\Enum\Routes;
use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class Factory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Serializer $redirectRouteSerializer,
    ) {
    }

    public function createSignInRedirectResponse(?string $userIdentifier, ?RedirectRoute $route): Response
    {
        $urlParameters = [];
        if (is_string($userIdentifier) && '' !== $userIdentifier) {
            $urlParameters['email'] = $userIdentifier;
        }

        if ($route instanceof RedirectRoute) {
            $urlParameters['route'] = $this->redirectRouteSerializer->serialize($route);
        }

        return $this->createRedirectResponse(
            $this->urlGenerator->generate(Routes::SIGN_IN_VIEW_NAME->value, $urlParameters)
        );
    }

    public function createDashboardRedirectResponse(): Response
    {
        return $this->createRedirectResponse(
            $this->urlGenerator->generate(Routes::DASHBOARD_NAME->value)
        );
    }

    private function createRedirectResponse(string $url): Response
    {
        return new Response(null, 302, [
            'content-type' => null,
            'location' => $url,
        ]);
    }
}
