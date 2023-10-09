<?php

declare(strict_types=1);

namespace App\Session;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class SessionDecodeExceptionHandler implements EventSubscriberInterface
{
    public function __construct(
        private Store $sessionStore,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', 0],
            ],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $message = $throwable->getMessage();

        if (!str_contains($message, 'session_')) {
            return;
        }

        if (!str_contains(strtolower($message), 'failed')) {
            return;
        }

        $this->sessionStore->set('');

        $event->setResponse(new Response(null, 302, [
            'location' => $this->urlGenerator->generate('dashboard'),
        ]));
    }
}
