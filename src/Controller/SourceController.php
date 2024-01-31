<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Enum\Routes;
use App\Exception\ApiException;
use App\FormError\Factory;
use App\Response\RedirectResponseFactory;
use App\Security\ApiKey;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\SourceClient;
use Symfony\Component\HttpFoundation\Request;
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
        private FileSourceClient $fileSourceClient,
        private RedirectResponseFactory $redirectResponseFactory,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route('/sources', name: Routes::SOURCES_NAME->value, methods: ['GET'])]
    public function index(ApiKey $apiKey, Request $request, Factory $formErrorFactory): Response
    {
        return new Response($this->twig->render('source/index.html.twig', [
            'sources' => $this->sourceClient->list($apiKey->key),
            'form_error' => $formErrorFactory->create(),
        ]));
    }

    /**
     * @throws ApiException
     */
    #[Route('/sources/file', name: 'sources_add_file_source', methods: ['POST'])]
    public function addFileSource(ApiKey $apiKey, Request $request): Response
    {
        try {
            $this->fileSourceClient->create($apiKey->key, $request->request->getString('label'));
        } catch (\Throwable $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return $this->redirectResponseFactory->createForRequest($request);
    }
}
