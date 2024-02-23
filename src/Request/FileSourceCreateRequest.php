<?php

declare(strict_types=1);

namespace App\Request;

readonly class FileSourceCreateRequest
{
    public function __construct(
        public string $label,
    ) {
    }
}
