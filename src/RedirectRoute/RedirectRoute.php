<?php

declare(strict_types=1);

namespace App\RedirectRoute;

readonly class RedirectRoute
{
    /**
     * @param array<string, int|string> $parameters
     */
    public function __construct(
        public string $name,
        public array $parameters = [],
    ) {
    }
}
