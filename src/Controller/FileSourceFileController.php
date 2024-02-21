<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Exception\ApiException;
use App\Request\FileSourceFileRequest;
use App\Response\RedirectResponse;
use App\Security\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\FileClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class FileSourceFileController
{
    public function __construct(
        private FileClient $fileClient,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @throws ApiException
     */
    #[Route(
        path: '/sources/file/{id<[A-Z90-9]{26}>}',
        name: 'sources_create_file_source_file',
        methods: ['POST']
    )]
    public function create(ApiKey $apiKey, FileSourceFileRequest $request): Response
    {
        try {
            $this->fileClient->create($apiKey->key, $request->sourceId, $request->filename, $request->content);
        } catch (ClientException $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('sources_view_file_source', ['id' => $request->sourceId])
        );
    }
}
