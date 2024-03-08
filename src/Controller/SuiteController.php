<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Exception\ApiException;
use App\Response\RedirectResponse;
use App\Security\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\SourceClient;
use SmartAssert\ApiClient\SuiteClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class SuiteController
{
    public function __construct(
        private TwigEnvironment $twig,
        private SourceClient $sourceClient,
        private SuiteClient $suiteClient,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws ApiException
     */
    #[Route('/suites', name: 'suites', methods: ['GET'])]
    public function index(ApiKey $apiKey): Response
    {
        try {
            $sources = $this->sourceClient->list($apiKey->key);
            $suites = $this->suiteClient->list($apiKey->key);
        } catch (ClientException $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new Response($this->twig->render(
            'suite/index.html.twig',
            [
                'sources' => $sources,
                'suites' => $suites,
            ]
        ));
    }

    #[Route('/suites', name: 'suite_create', methods: ['POST'])]
    public function create(): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('suites'));
    }
}
