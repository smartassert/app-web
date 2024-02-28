<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory\BadRequestErrorHandler;

use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;

class WrongTypeHandler implements TypeHandlerInterface
{
    public function create(string $formName, string $type, BadRequestErrorInterface $error): ?string
    {
        if ('wrong_type' !== $type) {
            return null;
        }

        return 'This value is of the wrong type.';
    }
}
