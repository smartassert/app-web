<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory\BadRequestErrorHandler;

use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;

class InvalidHandler implements TypeHandlerInterface
{
    public function create(string $formName, string $type, BadRequestErrorInterface $error): ?string
    {
        if ('invalid' !== $type) {
            return null;
        }

        $message = 'This value is invalid.';

        $requiredDataType = $error->getParameter()->getRequirements()?->getDataType();

        if (null !== $requiredDataType && 'string' !== $requiredDataType) {
            $message .= ' It must be a valid "' . $requiredDataType . '".';
        }

        return $message;
    }
}
