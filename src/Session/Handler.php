<?php

declare(strict_types=1);

namespace App\Session;

use App\Security\Encrypter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;

class Handler extends AbstractSessionHandler implements \SessionHandlerInterface
{
    public function __construct(
        private readonly Store $store,
        private readonly RequestStack $requestStack,
        private readonly Encrypter $encrypter,
    ) {
    }

    public function close(): bool
    {
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return true;
    }

    protected function doRead(#[\SensitiveParameter] string $sessionId): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $encryptedSessionData = $request?->cookies->get('session') ?? '';
        $sessionData = (string) $this->encrypter->decrypt($encryptedSessionData);

        $this->store->set($sessionData);

        return $sessionData;
    }

    protected function doWrite(#[\SensitiveParameter] string $sessionId, string $data): bool
    {
        $this->store->set($data);

        return true;
    }

    protected function doDestroy(#[\SensitiveParameter] string $sessionId): bool
    {
        return true;
    }
}
