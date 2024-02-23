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

    /**
     * @template T of object
     *
     * @param class-string<T> $expectedType
     *
     * @return null|T
     */
    public function get(string $expectedType): ?object
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        $session = $request->getSession();
        $payload = $session->get('payload');

        if (!$payload instanceof $expectedType) {
            return null;
        }

        $session->remove('payload');

        return $payload;
    }
}
