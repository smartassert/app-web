<?php

declare(strict_types=1);

namespace App\Security;

use App\RedirectRoute\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

readonly class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private Factory $redirectRouteFactory,
        private \App\SignInRedirectResponse\Factory $signInRedirectResponseFactory,
    ) {
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return $this->signInRedirectResponseFactory->create(
            userIdentifier: null,
            error: null,
            route: $this->redirectRouteFactory->createFromRequest($request)
        );
    }
}
