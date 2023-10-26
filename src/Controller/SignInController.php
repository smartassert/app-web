<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\Routes;
use App\Enum\SignInErrorState;
use App\RedirectRoute\Serializer;
use App\Request\SignInReadRequest;
use App\Response\Factory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    #[Route(Routes::SIGN_IN_VIEW_PATH->value, name: Routes::SIGN_IN_VIEW_NAME->value, methods: ['GET'])]
    public function view(
        SignInReadRequest $request,
        TwigEnvironment $twig,
        Serializer $redirectRouteSerializer
    ): Response {
        if (is_string($request->error) && !SignInErrorState::is($request->error)) {
            return $this->signInRedirectResponseFactory->create(
                userIdentifier: $request->email,
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
