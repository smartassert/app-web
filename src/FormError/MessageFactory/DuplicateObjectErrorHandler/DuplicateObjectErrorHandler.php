<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory\DuplicateObjectErrorHandler;

use App\FormError\MessageFactory\ErrorHandlerInterface;
use SmartAssert\ServiceRequest\Error\DuplicateObjectErrorInterface;
use SmartAssert\ServiceRequest\Error\ErrorInterface;

class DuplicateObjectErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var iterable<TypeHandlerInterface>
     */
    private iterable $handlers;

    /**
     * @param iterable<TypeHandlerInterface> $handlers
     */
    public function __construct(iterable $handlers)
    {
        $filteredHandlers = [];

        foreach ($handlers as $handler) {
            if ($handler instanceof TypeHandlerInterface) {
                $filteredHandlers[] = $handler;
            }
        }

        $this->handlers = $filteredHandlers;
    }

    public function create(string $formName, ErrorInterface $error): ?string
    {
        if (!$error instanceof DuplicateObjectErrorInterface) {
            return null;
        }

        foreach ($this->handlers as $handler) {
            $message = $handler->create($formName, $error);

            if (is_string($message)) {
                return $message;
            }
        }

        return 'duplicate object!';
    }
}
