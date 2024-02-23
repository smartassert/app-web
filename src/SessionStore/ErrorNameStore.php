<?php

declare(strict_types=1);

namespace App\SessionStore;

use Symfony\Component\HttpFoundation\RequestStack;

readonly class ErrorNameStore
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function set(string $name): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $request->getSession()->set('error_name', $name);
    }

    public function get(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        $session = $request->getSession();

        $name = $session->get('error_name');
        $session->remove('error_name');

        return is_string($name) ? $name : null;
    }
}
