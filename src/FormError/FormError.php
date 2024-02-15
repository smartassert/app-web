<?php

declare(strict_types=1);

namespace App\FormError;

readonly class FormError
{
    public function __construct(
        public string $formName,
        public ?string $fieldName,
        public string $message,
    ) {
    }
}
