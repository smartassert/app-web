<?php

declare(strict_types=1);

namespace App\Security\TokenAuthentication;

use App\RedirectRoute\Factory as RedirectRouteFactory;
use App\SignInRedirectResponse\Factory as SignInRedirectResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

readonly class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private RedirectRouteFactory $redirectRouteFactory,
        private SignInRedirectResponseFactory $signInRedirectResponseFactory,
    ) {
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return $this->signInRedirectResponseFactory->create(
            userIdentifier: null,
            route: $this->redirectRouteFactory->createFromRequest($request)
        );
    }
}
