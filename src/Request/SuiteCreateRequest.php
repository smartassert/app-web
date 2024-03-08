<?php

declare(strict_types=1);

namespace App\Request;

readonly class SuiteCreateRequest
{
    /**
     * @param string[] $tests
     */
    public function __construct(
        public string $label,
        public string $sourceId,
        public array $tests,
    ) {
    }
}
