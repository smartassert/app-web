<?php

declare(strict_types=1);

namespace App\Session;

use App\Security\Encrypter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class ResponseWriter implements EventSubscriberInterface
{
    public function __construct(
        private Store $sessionStore,
        private Encrypter $encrypter,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                ['onKernelResponse', -5000],
            ],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        $sessionData = $this->sessionStore->get();
        $encryptedSessionData = $this->encrypter->encrypt($sessionData);

        $response->headers->setCookie(Cookie::create('session', $encryptedSessionData));

        $event->setResponse($response);
    }
}
