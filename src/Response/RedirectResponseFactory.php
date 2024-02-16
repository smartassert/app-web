<?php

declare(strict_types=1);

namespace App\Response;

use App\Enum\Routes;
use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class RedirectResponseFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Serializer $redirectRouteSerializer,
        private TargetMapper $targetMapper,
    ) {
    }

    public function createForSignIn(?string $userIdentifier, ?RedirectRoute $route): RedirectResponse
    {
        $urlParameters = [];
        if (is_string($userIdentifier) && '' !== $userIdentifier) {
            $urlParameters['email'] = $userIdentifier;
        }

        if ($route instanceof RedirectRoute) {
            $urlParameters['route'] = $this->redirectRouteSerializer->serialize($route);
        }

        $response = new RedirectResponse(
            $this->urlGenerator->generate(Routes::SIGN_IN_VIEW_NAME->value, $urlParameters)
        );

        $response->headers->setCookie(Cookie::create('token'));

        return $response;
    }

    public function create(RedirectRoute $redirectRoute): RedirectResponse
    {
        return new RedirectResponse(
            $this->urlGenerator->generate($redirectRoute->name, $redirectRoute->parameters),
        );
    }

    public function createForRequest(Request $request): RedirectResponse
    {
        $targetRoute = $this->targetMapper->getForRequest($request);
        if (null === $targetRoute) {
            $targetRoute = 'dashboard';
        }

        return $this->create(new RedirectRoute($targetRoute));
    }
}
