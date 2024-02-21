<?php

declare(strict_types=1);

namespace App\Request;

readonly class FileSourceFileRequest
{
    /**
     * @param non-empty-string $sourceId
     */
    public function __construct(
        public string $sourceId,
        public string $filename,
        public string $content,
    ) {
    }
}
