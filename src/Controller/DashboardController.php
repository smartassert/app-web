<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\Routes;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class DashboardController
{
    public function __construct(
        private TwigEnvironment $twig,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route('/', name: Routes::DASHBOARD_NAME->value, methods: ['GET'])]
    public function index(): Response
    {
        return new Response($this->twig->render('dashboard/index.html.twig'));
    }
}
