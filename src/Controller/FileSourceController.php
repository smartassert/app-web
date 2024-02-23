<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Exception\ApiException;
use App\FormError\Factory;
use App\Response\RedirectResponse;
use App\Security\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\SourceClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class FileSourceController
{
    public function __construct(
        private TwigEnvironment $twig,
        private SourceClient $sourceClient,
        private FileSourceClient $fileSourceClient,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param non-empty-string $id
     *
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws ApiException
     */
    #[Route('/sources/file/{id<[A-Z90-9]{26}>}', name: 'sources_view_file_source', methods: ['GET'])]
    public function view(ApiKey $apiKey, Factory $formErrorFactory, string $id): Response
    {
        try {
            $source = $this->sourceClient->get($apiKey->key, $id);
            $files = $this->fileSourceClient->list($apiKey->key, $id);
        } catch (ClientException $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new Response($this->twig->render(
            'source/file_source/index.html.twig',
            [
                'source' => $source,
                'files' => $files,
                'form_error' => $formErrorFactory->create(),
            ]
        ));
    }

    /**
     * @throws ApiException
     */
    #[Route('/sources/file', name: 'sources_create_file_source', methods: ['POST'])]
    public function create(ApiKey $apiKey, Request $request): Response
    {
        $response = new RedirectResponse($this->urlGenerator->generate('sources'));

        try {
            $this->fileSourceClient->create($apiKey->key, $request->request->getString('label'));
        } catch (\Throwable $e) {
            throw new ApiException(ApiService::SOURCES, $e, $response);
        }

        return $response;
    }
}
