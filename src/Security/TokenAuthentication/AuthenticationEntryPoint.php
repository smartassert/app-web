<?php

declare(strict_types=1);

namespace App\Security\TokenAuthentication;

use App\RedirectRoute\Factory as RedirectRouteFactory;
use App\Response\RedirectResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

readonly class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private RedirectRouteFactory $redirectRouteFactory,
        private RedirectResponseFactory $redirectResponseFactory,
    ) {
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return $this->redirectResponseFactory->createForSignIn(
            userIdentifier: null,
            route: $this->redirectRouteFactory->createFromRequest($request)
        );
    }
}
