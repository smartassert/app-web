<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Enum\Routes;
use App\Exception\ApiException;
use App\Security\ApiKey;
use SmartAssert\ApiClient\Exception\ClientExceptionInterface;
use SmartAssert\ApiClient\SourceClient;
use SmartAssert\ApiClient\SuiteClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class DashboardController
{
    public function __construct(
        private TwigEnvironment $twig,
        private SourceClient $sourceClient,
        private SuiteClient $suiteClient,
    ) {}

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws ApiException
     */
    #[Route('/', name: Routes::DASHBOARD_NAME->value, methods: ['GET'])]
    public function index(ApiKey $apiKey): Response
    {
        try {
            $sources = $this->sourceClient->list($apiKey->key);
            $suites = $this->suiteClient->list($apiKey->key);
        } catch (ClientExceptionInterface $clientException) {
            throw new ApiException(ApiService::SOURCES, $clientException);
        }

        return new Response($this->twig->render(
            'dashboard/index.html.twig',
            [
                'sources' => $sources,
                'suites' => $suites,
            ]
        ));
    }
}
