<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory\BadRequestErrorHandler;

use App\FormError\MessageFactory\ErrorHandlerInterface;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Error\ErrorInterface;

class BadRequestErrorHandler implements ErrorHandlerInterface
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

    public function create(ErrorInterface $error): ?string
    {
        if (!$error instanceof BadRequestErrorInterface) {
            return null;
        }

        $type = $error->getType();
        if (is_string($type)) {
            foreach ($this->handlers as $handler) {
                $message = $handler->create($type, $error);

                if (is_string($message)) {
                    return $message;
                }
            }
        }

        return 'bad request!';
    }
}
