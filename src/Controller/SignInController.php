<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\SignInErrorState;
use App\RedirectRoute\Serializer;
use App\Request\SignInReadRequest;
use App\SignInRedirectResponse\Factory;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class SignInController
{
    public function __construct(
        private Factory $signInRedirectResponseFactory,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function view(
        SignInReadRequest $request,
        TwigEnvironment $twig,
        Serializer $redirectRouteSerializer
    ): Response {
        if (is_string($request->error) && !SignInErrorState::is($request->error)) {
            return $this->signInRedirectResponseFactory->create(
                userIdentifier: $request->email,
                error: null,
                route: $request->route,
            );
        }

        $viewParameters = [
            'email' => $request->email,
            'route' => $redirectRouteSerializer->serialize($request->route),
            'error' => $request->error,
        ];

        return new Response($twig->render('sign_in/index.html.twig', $viewParameters));
    }
}
