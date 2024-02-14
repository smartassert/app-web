<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\ApiException;
use App\RedirectRoute\Factory;
use App\Response\RedirectResponseFactory;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\User\UserInterface;

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
        if ($innerException instanceof UnauthorizedException) {
            $user = $this->security->getUser();
            $userIdentifier = $user instanceof UserInterface ? $user->getUserIdentifier() : null;

            $response = $this->redirectResponseFactory->createForSignIn(
                $userIdentifier,
                $this->redirectRouteFactory->getDefault(),
            );
        }

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }
}
