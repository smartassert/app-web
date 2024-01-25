<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Enum\SignInErrorState;
use App\Exception\ApiException;
use SmartAssert\ApiClient\Exception\ErrorExceptionInterface;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
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

        $session = $event->getRequest()->getSession();
        if (!$session instanceof Session) {
            return;
        }

        $exception = $throwable->exception;
        if ($exception instanceof ErrorExceptionInterface) {
            $this->handleErrorException($exception, $session);
        }

        if ($exception instanceof UnauthorizedException) {
            $this->handleUnauthorizedException($session);
        }
    }

    private function handleErrorException(ErrorExceptionInterface $exception, Session $session): void
    {
        $session->getFlashBag()->set('error_name', $exception->getName());
        $session->set('error', $exception->getError());
    }

    private function handleUnauthorizedException(Session $session): void
    {
        $session->getFlashBag()->set('error', SignInErrorState::API_UNAUTHORIZED->value);
    }
}
