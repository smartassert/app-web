<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Exception\ApiException;
use App\FormError\Factory;
use App\Request\FileSourceFileRequest;
use App\Response\RedirectResponse;
use App\Security\ApiKey;
use App\SessionStore\RequestPayloadStore;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\FileClient;
use SmartAssert\ApiClient\SourceClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route('/sources/file/{id<[A-Z90-9]{26}>}', name: 'sources_file_source_file_')]
readonly class FileSourceFileController
{
    public function __construct(
        private TwigEnvironment $twig,
        private SourceClient $sourceClient,
        private FileClient $fileClient,
        private UrlGeneratorInterface $urlGenerator,
        private RequestPayloadStore $requestPayloadStore,
    ) {
    }

    /**
     * @param non-empty-string $id
     * @param non-empty-string $filename
     *
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws ApiException
     */
    #[Route(path: '/{filename}', name: 'view', methods: ['GET'])]
    public function view(ApiKey $apiKey, Factory $formErrorFactory, string $id, string $filename): Response
    {
        try {
            $source = $this->sourceClient->get($apiKey->key, $id);
            $content = $this->fileClient->read($apiKey->key, $id, $filename);
        } catch (ClientException $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new Response($this->twig->render(
            'source/file_source/file.html.twig',
            [
                'source' => $source,
                'filename' => $filename,
                'content' => $content,
                'form_error' => $formErrorFactory->create(),
                'file_source_file_request' => $this->requestPayloadStore->get(FileSourceFileRequest::class),
            ]
        ));
    }

    /**
     * @throws ApiException
     */
    #[Route(name: 'create', methods: ['POST'])]
    public function create(
        ApiKey $apiKey,
        FileSourceFileRequest $request,
        RequestPayloadStore $requestPayloadStore
    ): Response {
        $response = new RedirectResponse(
            $this->urlGenerator->generate('sources_view_file_source', ['id' => $request->sourceId])
        );

        try {
            $this->fileClient->create($apiKey->key, $request->sourceId, $request->filename, $request->content);
        } catch (ClientException $e) {
            $requestPayloadStore->set($request);

            throw new ApiException(ApiService::SOURCES, $e, $response);
        }

        return $response;
    }

    /**
     * @throws ApiException
     */
    #[Route(path: '/{filename}', name: 'update', methods: ['POST'])]
    public function update(
        ApiKey $apiKey,
        FileSourceFileRequest $request,
        RequestPayloadStore $requestPayloadStore
    ): Response {
        $response = new RedirectResponse(
            $this->urlGenerator->generate(
                'sources_file_source_file_view',
                [
                    'id' => $request->sourceId,
                    'filename' => $request->filename,
                ]
            )
        );

        try {
            $this->fileClient->update($apiKey->key, $request->sourceId, $request->filename, $request->content);
        } catch (ClientException $e) {
            $requestPayloadStore->set($request);

            throw new ApiException(ApiService::SOURCES, $e, $response);
        }

        return $response;
    }
}
