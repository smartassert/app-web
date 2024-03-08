<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Exception\ApiException;
use App\Security\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\SuiteClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class SuiteController
{
    public function __construct(
        private TwigEnvironment $twig,
        private SuiteClient $suiteClient,
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
            $suites = $this->suiteClient->list($apiKey->key);
        } catch (ClientException $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new Response($this->twig->render(
            'suite/index.html.twig',
            [
                'suites' => $suites,
            ]
        ));
    }
}
