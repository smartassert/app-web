<?php

declare(strict_types=1);

namespace App\SessionStore;

use Symfony\Component\HttpFoundation\RequestStack;

readonly class RequestPayloadStore
{
    public function __construct(
        private RequestStack $requestStack,
    ) {}

    public function set(object $payload, string $name): void
    {
        $this->requestStack->getCurrentRequest()?->getSession()->set($name, $payload);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $expectedType
     *
     * @return null|T
     */
    public function get(string $expectedType, string $name): ?object
    {
        $payload = $this->peek($expectedType, $name);

        if (null !== $payload) {
            $this->requestStack->getCurrentRequest()?->getSession()->remove($name);
        }

        return $payload;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $expectedType
     *
     * @return null|T
     */
    public function peek(string $expectedType, string $name): ?object
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        $session = $request->getSession();
        $payload = $session->get($name);

        if (!$payload instanceof $expectedType) {
            return null;
        }

        return $payload;
    }
}
