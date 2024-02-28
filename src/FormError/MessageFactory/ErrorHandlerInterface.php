<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory;

use SmartAssert\ServiceRequest\Error\ErrorInterface;

interface ErrorHandlerInterface
{
    public function create(string $formName, ErrorInterface $error): ?string;
}
