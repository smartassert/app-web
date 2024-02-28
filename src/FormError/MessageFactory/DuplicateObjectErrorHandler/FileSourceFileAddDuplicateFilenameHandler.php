<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory\DuplicateObjectErrorHandler;

use App\Request\FileSourceFileRequest;
use App\SessionStore\RequestPayloadStore;
use SmartAssert\ServiceRequest\Error\DuplicateObjectErrorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class FileSourceFileAddDuplicateFilenameHandler implements TypeHandlerInterface
{
    public function __construct(
        private RequestPayloadStore $requestPayloadStore,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function create(string $formName, DuplicateObjectErrorInterface $error): ?string
    {
        if ('file_source_file_add' !== $formName) {
            return null;
        }

        $fileSourceFileRequest = $this->requestPayloadStore->peek(FileSourceFileRequest::class);
        if (!$fileSourceFileRequest instanceof FileSourceFileRequest) {
            return null;
        }

        $url = $this->urlGenerator->generate(
            'sources_file_source_file_view',
            [
                'id' => $fileSourceFileRequest->sourceId,
                'filename' => $fileSourceFileRequest->filename,
            ]
        );

        return sprintf(
            'File source "%s" already has a file named "%s".',
            $fileSourceFileRequest->sourceId,
            '<a href="' . $url . '">' . $fileSourceFileRequest->filename . '</a>',
        );
    }
}
