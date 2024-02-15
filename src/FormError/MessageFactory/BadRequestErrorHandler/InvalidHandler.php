<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory\BadRequestErrorHandler;

use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;

class InvalidHandler implements TypeHandlerInterface
{
    public function create(string $type, BadRequestErrorInterface $error): ?string
    {
        if ('invalid' !== $type) {
            return null;
        }

        return 'This value is invalid.';
    }
}
