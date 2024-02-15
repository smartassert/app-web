<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Enum\Routes;
use App\Exception\ApiException;
use App\FormError\Factory;
use App\Security\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\SourceClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class SourceController
{
    public function __construct(
        private TwigEnvironment $twig,
        private SourceClient $sourceClient,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws ApiException
     */
    #[Route('/sources', name: Routes::SOURCES_NAME->value, methods: ['GET'])]
    public function index(ApiKey $apiKey, Factory $formErrorFactory): Response
    {
        try {
            $sources = $this->sourceClient->list($apiKey->key);
        } catch (ClientException $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new Response($this->twig->render(
            'source/index.html.twig',
            [
                'sources' => $sources,
                'form_error' => $formErrorFactory->create(),
            ]
        ));
    }
}
