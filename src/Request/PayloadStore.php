<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\RequestStack;

readonly class PayloadStore
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function set(object $payload): void
    {
        $this->requestStack->getCurrentRequest()?->getSession()->set('payload', $payload);
    }

    public function get(): ?object
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        $payload = $request->getSession()->get('payload');

        return is_object($payload) ? $payload : null;
    }
}
