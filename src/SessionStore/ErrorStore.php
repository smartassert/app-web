<?php

declare(strict_types=1);

namespace App\SessionStore;

use SmartAssert\ServiceRequest\Error\ErrorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class ErrorStore
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function set(ErrorInterface $error): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $request->getSession()->set('error', $error);
    }

    public function get(): ?ErrorInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        $session = $request->getSession();

        $error = $session->get('error');
        $session->remove('error');

        return $error instanceof ErrorInterface ? $error : null;
    }
}
