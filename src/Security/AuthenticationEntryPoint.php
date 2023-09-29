<?php

declare(strict_types=1);

namespace App\Security;

use App\RedirectRoute\Factory;
use App\RedirectRoute\Serializer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

readonly class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private Factory $redirectRouteFactory,
        private UrlGeneratorInterface $urlGenerator,
        private Serializer $redirectRouteSerializer,
    ) {
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $redirectRoute = $this->redirectRouteFactory->createFromRequest($request);

        return new RedirectResponse($this->urlGenerator->generate(
            'sign_in_view',
            ['route' => $this->redirectRouteSerializer->serialize($redirectRoute)]
        ));
    }
}
