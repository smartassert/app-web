<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory\BadRequestErrorHandler;

use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;

interface TypeHandlerInterface
{
    public function create(string $type, BadRequestErrorInterface $error): ?string;
}
