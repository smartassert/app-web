<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory;

use SmartAssert\ServiceRequest\Error\ErrorInterface;

readonly class MessageFactory
{
    /**
     * @var iterable<ErrorHandlerInterface>
     */
    private iterable $handlers;

    /**
     * @param iterable<ErrorHandlerInterface> $handlers
     */
    public function __construct(iterable $handlers)
    {
        $filteredErrorHandlers = [];

        foreach ($handlers as $errorHandler) {
            if ($errorHandler instanceof ErrorHandlerInterface) {
                $filteredErrorHandlers[] = $errorHandler;
            }
        }

        $this->handlers = $filteredErrorHandlers;
    }

    public function generate(ErrorInterface $error): string
    {
        foreach ($this->handlers as $handler) {
            $message = $handler->create($error);

            if (is_string($message)) {
                return $message;
            }
        }

        return $error::class . ' rendered error';
    }
}
