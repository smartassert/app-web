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

        $url = $this->urlGenerator->generate(Routes::SIGN_IN_VIEW_NAME->value, $urlParameters);

        return new Response(null, 302, [
            'content-type' => null,
            'location' => $url,
        ]);
    }

    public function createDashboardRedirectResponse(): Response
    {
        $url = $this->urlGenerator->generate(Routes::DASHBOARD_NAME->value);

        return new Response(null, 302, [
            'content-type' => null,
            'location' => $url,
        ]);
    }
}
