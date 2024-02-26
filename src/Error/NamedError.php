<?php

declare(strict_types=1);

namespace App\Error;

use SmartAssert\ServiceRequest\Error\ErrorInterface;

readonly class NamedError
{
    public function __construct(
        public string $name,
        public ?ErrorInterface $error = null,
    ) {
    }
}
