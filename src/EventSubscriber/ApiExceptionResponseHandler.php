<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\ApiException;
use App\RedirectRoute\Factory;
use App\Response\RedirectResponseFactory;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class ApiExceptionResponseHandler implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private RedirectResponseFactory $redirectResponseFactory,
        private Factory $redirectRouteFactory,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['handleApiException', 0],
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

        $response = null;

        $innerException = $clientException->getInnerException();
        if ($innerException instanceof ErrorException) {
            $response = $this->redirectResponseFactory->createForRequest($event->getRequest());
        }

        if ($innerException instanceof UnauthorizedException) {
            $response = $this->handleUnauthorizedException();
        }

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    private function handleUnauthorizedException(): ?Response
    {
        $user = $this->security->getUser();
        if (null === $user) {
            return null;
        }

        return $this->redirectResponseFactory->createForSignIn(
            $user->getUserIdentifier(),
            $this->redirectRouteFactory->getDefault(),
        );
    }
}
