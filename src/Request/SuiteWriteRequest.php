<?php

declare(strict_types=1);

namespace App\Request;

readonly class SuiteWriteRequest
{
    /**
     * @param string[] $tests
     */
    public function __construct(
        public ?string $id,
        public string $label,
        public string $sourceId,
        public array $tests,
    ) {}
}
