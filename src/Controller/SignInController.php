<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\Routes;
use App\Enum\SignInErrorState;
use App\RedirectRoute\Serializer;
use App\Request\SignInReadRequest;
use App\Response\RedirectResponseFactory;
use App\SessionStore\ErrorStore;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class SignInController
{
    public function __construct(
        private RedirectResponseFactory $redirectResponseFactory,
        private ErrorStore $errorStore,
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
        $errorName = $this->errorStore->get()?->name;

        if (is_string($errorName) && !SignInErrorState::is($errorName)) {
            return $this->redirectResponseFactory->createForSignIn(
                userIdentifier: $request->email,
                route: $request->route,
            );
        }

        $viewParameters = [
            'email' => $request->email,
            'route' => $redirectRouteSerializer->serialize($request->route),
            'error' => $errorName,
        ];

        return new Response($twig->render('sign_in/index.html.twig', $viewParameters));
    }
}
