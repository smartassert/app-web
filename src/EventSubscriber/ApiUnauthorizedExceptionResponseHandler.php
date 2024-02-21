<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Enum\SignInErrorState;
use App\Exception\ApiException;
use App\RedirectRoute\Factory;
use App\Response\RedirectResponseFactory;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class ApiUnauthorizedExceptionResponseHandler implements EventSubscriberInterface
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
                ['handleApiUnauthorizedException', 1000],
            ],
        ];
    }

    public function handleApiUnauthorizedException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof ApiException) {
            return;
        }

        $clientException = $throwable->exception;
        if (!$clientException instanceof ClientException) {
            return;
        }

        $clientException = $throwable->exception;
        if (!$clientException instanceof ClientException) {
            return;
        }

        $unauthorizedException = $clientException->getInnerException();
        if (!$unauthorizedException instanceof UnauthorizedException) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->set('error', SignInErrorState::API_UNAUTHORIZED->value);
        }

        $user = $this->security->getUser();
        $userIdentifier = $user instanceof UserInterface ? $user->getUserIdentifier() : null;

        $event->setResponse($this->redirectResponseFactory->createForSignIn(
            $userIdentifier,
            $this->redirectRouteFactory->getDefault(),
        ));
    }
}
