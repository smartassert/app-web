<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory\DuplicateObjectErrorHandler;

use SmartAssert\ServiceRequest\Error\DuplicateObjectErrorInterface;

interface TypeHandlerInterface
{
    public function create(string $formName, DuplicateObjectErrorInterface $error): ?string;
}
