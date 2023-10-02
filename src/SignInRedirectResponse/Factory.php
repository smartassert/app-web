<?php

declare(strict_types=1);

namespace App\SignInRedirectResponse;

use App\Enum\SignInErrorState;
use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class Factory
{
    private const ROUTE_NAME = 'sign_in_view';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Serializer $redirectRouteSerializer,
    ) {
    }

    public function create(?string $userIdentifier, ?string $error, ?RedirectRoute $route): Response
    {
        $urlParameters = [];
        if (is_string($userIdentifier) && '' !== $userIdentifier) {
            $urlParameters['email'] = $userIdentifier;
        }

        if (is_string($error) && $this->isErrorState($error)) {
            $urlParameters['error'] = $error;
        }

        if ($route instanceof RedirectRoute) {
            $urlParameters['route'] = $this->redirectRouteSerializer->serialize($route);
        }

        $url = $this->urlGenerator->generate(self::ROUTE_NAME, $urlParameters);

        return new Response(null, 302, [
            'content-type' => null,
            'location' => $url,
        ]);
    }

    private function isErrorState(string $error): bool
    {
        foreach (SignInErrorState::cases() as $errorState) {
            if ($error === $errorState->value) {
                return true;
            }
        }

        return false;
    }
}
