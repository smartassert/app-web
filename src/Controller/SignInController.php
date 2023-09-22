<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SignInController
{
    public function __construct(
        private readonly TwigEnvironment $twig,
        private readonly RouterInterface $router,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route('/sign-in/', name: 'sign-in', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        if (Request::METHOD_POST === $request->getMethod()) {
            return new RedirectResponse($this->router->generate('sign-in'));
        }

        return new Response($this->twig->render('sign_in/index.html.twig'));
    }
}
