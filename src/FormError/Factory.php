<?php

declare(strict_types=1);

namespace App\FormError;

use App\FormError\MessageFactory\MessageFactory;
use SmartAssert\ServiceRequest\Error\ErrorInterface;
use SmartAssert\ServiceRequest\Error\HasParameterInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

readonly class Factory
{
    /**
     * @param array<string, string> $actionToFormMap
     */
    public function __construct(
        private RequestStack $requestStack,
        private array $actionToFormMap,
        private MessageFactory $messageFactory,
    ) {
    }

    public function create(): ?FormError
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        try {
            $session = $request->getSession();
        } catch (SessionNotFoundException) {
            return null;
        }

        if (!$session instanceof FlashBagAwareSessionInterface) {
            return null;
        }

        $actions = $session->getFlashBag()->get('error_name');
        $action = $actions[0] ?? null;
        if (null === $action) {
            return null;
        }

        $formName = $this->actionToFormMap[$action] ?? null;
        if (!is_string($formName)) {
            return null;
        }

        $error = $request->getSession()->get('error');
        if (!$error instanceof ErrorInterface) {
            return null;
        }

        $fieldName = $error instanceof HasParameterInterface
            ? $error->getParameter()->getName()
            : null;

        return new FormError($formName, $fieldName, $this->messageFactory->generate($error));
    }
}
