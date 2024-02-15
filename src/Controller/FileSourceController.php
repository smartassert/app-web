<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Exception\ApiException;
use App\Response\RedirectResponseFactory;
use App\Security\ApiKey;
use SmartAssert\ApiClient\FileSourceClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class FileSourceController
{
    public function __construct(
        private FileSourceClient $fileSourceClient,
        private RedirectResponseFactory $redirectResponseFactory,
    ) {
    }

    /**
     * @throws ApiException
     */
    #[Route('/sources/file', name: 'sources_add_file_source', methods: ['POST'])]
    public function add(ApiKey $apiKey, Request $request): Response
    {
        try {
            $this->fileSourceClient->create($apiKey->key, $request->request->getString('label'));
        } catch (\Throwable $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return $this->redirectResponseFactory->createForRequest($request);
    }
}
