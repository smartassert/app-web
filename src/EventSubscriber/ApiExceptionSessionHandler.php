<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\ApiException;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class ApiExceptionSessionHandler implements EventSubscriberInterface
{
    /**
     * @return array<mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['handleApiException', 100],
            ],
        ];
    }

    public function handleApiException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof ApiException) {
            return;
        }

        $clientException = $throwable->exception;
        if (!$clientException instanceof ClientException) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if (!$session instanceof Session) {
            return;
        }

        $innerException = $clientException->getInnerException();
        if ($innerException instanceof ErrorException) {
            $session->getFlashBag()->set('error_name', $clientException->getRequestName());
            $session->set('error', $innerException->getError());
        }
    }
}
