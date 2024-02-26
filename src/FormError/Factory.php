<?php

declare(strict_types=1);

namespace App\FormError;

use App\Error\NamedError;
use App\FormError\MessageFactory\MessageFactory;
use App\SessionStore\ErrorStore;
use SmartAssert\ServiceRequest\Error\ErrorInterface;
use SmartAssert\ServiceRequest\Error\HasParameterInterface;

readonly class Factory
{
    /**
     * @param array<string, string> $actionToFormMap
     */
    public function __construct(
        private array $actionToFormMap,
        private MessageFactory $messageFactory,
        private ErrorStore $errorStore,
    ) {
    }

    public function create(): ?FormError
    {
        $error = $this->errorStore->get();
        if (!$error instanceof NamedError) {
            return null;
        }

        $formName = $this->actionToFormMap[$error->name] ?? null;
        if (!is_string($formName)) {
            return null;
        }

        $innerError = $error->error;
        if (!$innerError instanceof ErrorInterface) {
            return null;
        }

        $fieldName = $innerError instanceof HasParameterInterface
            ? $innerError->getParameter()->getName()
            : null;

        return new FormError($formName, $fieldName, $this->messageFactory->generate($innerError));
    }
}
