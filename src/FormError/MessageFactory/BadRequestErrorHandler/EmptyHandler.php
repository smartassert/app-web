<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory\BadRequestErrorHandler;

use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;

class EmptyHandler implements TypeHandlerInterface
{
    public function create(string $type, BadRequestErrorInterface $error): ?string
    {
        if ('empty' !== $type) {
            return null;
        }

        return 'This value must not be empty.';
    }
}
