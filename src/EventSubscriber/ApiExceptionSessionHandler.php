<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\ApiException;
use App\SessionStore\ErrorNameStore;
use App\SessionStore\ErrorStore;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class ApiExceptionSessionHandler implements EventSubscriberInterface
{
    public function __construct(
        private ErrorNameStore $errorNameStore,
        private ErrorStore $errorStore,
    ) {
    }

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

        $this->errorNameStore->set($clientException->getRequestName());

        $innerException = $clientException->getInnerException();
        if ($innerException instanceof ErrorException) {
            $this->errorStore->set($innerException->getError());
        }
    }
}
